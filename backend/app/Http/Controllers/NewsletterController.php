<?php

namespace App\Http\Controllers;

use App\Application\Newsletter\NewsletterAssetService;
use App\Application\Newsletter\NewsletterComposer;
use App\Application\Newsletter\NewsletterLayouts;
use App\Application\Newsletter\NewsletterPdfResult;
use App\Application\Newsletter\NewsletterPdfService;
use App\Contracts\Repositories\NewsletterHistoryRepositoryContract;
use App\Domain\Newsletter\NewsletterComposition;
use App\Domain\Newsletter\NewsletterGenerationRecord;
use App\Domain\Newsletter\NewsletterLayout;
use DateTimeImmutable;
use Illuminate\Http\Request;

/**
 * CoJ-MIP newsletter studio.
 *
 * The studio composes a newsletter from the standard selection pipeline for
 * a user topic, then lets the user switch between display layouts and
 * generate a validated exactly-three-page PDF. Composition is stored in the
 * session, so switching layouts NEVER re-selects or recomposes data (the
 * composition value object is simply re-rendered under a different layout).
 * The PDF render writes a newsletter generation record to the history
 * repository; merely composing a draft never writes any history.
 */
class NewsletterController extends Controller
{
    public const SESSION_COMPOSITION = 'newsletter.composition';

    public const SESSION_PDF_STATUS = 'newsletter.pdf_status';

    public function __construct(
        private NewsletterComposer $composer,
        private NewsletterLayouts $layouts,
        private NewsletterPdfService $pdf,
        private NewsletterHistoryRepositoryContract $history,
        private NewsletterAssetService $assets,
    ) {}

    public function index(Request $request)
    {
        $composition = $this->compositionFromSession($request);
        $layoutCode = $composition?->layout->value ?? $this->layouts->default();

        return view('newsletter.index', [
            'composition' => $composition,
            'layoutCode' => $layoutCode,
            'currentLayout' => $this->layouts->get($layoutCode)
                ?? ['name' => '', 'tag' => '', 'blurb' => ''],
            'layouts' => $this->layouts->all(),
            'defaultLayout' => $this->layouts->default(),
            'pdfStatus' => $request->session()->get(self::SESSION_PDF_STATUS),
            'masthead' => $this->assets->masthead(),
        ]);
    }

    public function compose(Request $request)
    {
        $data = $request->validate([
            'topic' => ['required', 'string', 'max:120'],
        ]);

        $composition = $this->composer->compose($data['topic']);

        $request->session()->put(self::SESSION_COMPOSITION, $composition->toArray());
        $request->session()->forget(self::SESSION_PDF_STATUS);

        return redirect()->route('newsletter.studio');
    }

    public function reset(Request $request)
    {
        $request->session()->forget(self::SESSION_COMPOSITION);
        $request->session()->forget(self::SESSION_PDF_STATUS);

        return redirect()->route('newsletter.studio');
    }

    public function setLayout(Request $request, string $layout)
    {
        $composition = $this->compositionFromSession($request);
        if ($composition === null) {
            return redirect()->route('newsletter.studio');
        }

        $normalised = $this->layouts->normalize($layout);

        $request->session()->put(
            self::SESSION_COMPOSITION,
            $composition->withLayout(NewsletterLayout::fromSlug($normalised))->toArray(),
        );

        return redirect()->route('newsletter.studio');
    }

    public function downloadPdf(Request $request)
    {
        $composition = $this->compositionFromSession($request);
        if ($composition === null) {
            return redirect()->route('newsletter.studio');
        }

        $result = $this->pdf->render($composition, $composition->layout->value);

        $this->record($composition, $result);

        $request->session()->put(self::SESSION_PDF_STATUS, [
            'valid' => $result->valid,
            'pages' => $result->pageCount,
            'message' => $result->message,
            'layout' => $composition->layout->value,
        ]);

        $filename = config('cojmip.newsletter.pdf.filename_prefix', 'coj-mip-newsletter')
            .'-'.$composition->id.'-'.date('Ymd-His').'.pdf';

        return response($result->bytes, $result->valid ? 200 : 422, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-cache',
            'X-Pdf-Pages' => (string) $result->pageCount,
            'X-Pdf-Valid' => $result->valid ? 'true' : 'false',
        ]);
    }

    private function compositionFromSession(Request $request): ?NewsletterComposition
    {
        $data = $request->session()->get(self::SESSION_COMPOSITION);

        return is_array($data) ? NewsletterComposition::fromArray($data) : null;
    }

    private function record(NewsletterComposition $composition, NewsletterPdfResult $result): void
    {
        $organisations = [];
        $articleCount = 0;

        foreach ($composition->organisations() as $organisation) {
            $articles = [];
            foreach ($organisation->articles as $article) {
                $articles[] = ['id' => $article->articleId, 'image_source' => $article->image->source];
                $articleCount++;
            }

            $organisations[] = [
                'id' => $organisation->organisationId,
                'name' => $organisation->name,
                'rank' => $organisation->rank,
                'score' => $organisation->score,
                'verification' => $organisation->verification,
                'logo_source' => $organisation->logo->source,
                'articles' => $articles,
            ];
        }

        $this->history->save(new NewsletterGenerationRecord(
            id: $composition->id,
            topic: $composition->topic,
            layout: $composition->layout->value,
            organisations: $organisations,
            articleCount: $articleCount,
            pdfStatus: $result->valid
                ? NewsletterGenerationRecord::PDF_SUCCESS
                : NewsletterGenerationRecord::PDF_FAILED,
            pageCount: $result->pageCount,
            message: $result->message,
            generatedAt: new DateTimeImmutable('now'),
        ));
    }
}
