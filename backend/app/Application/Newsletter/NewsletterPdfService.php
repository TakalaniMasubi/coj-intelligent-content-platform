<?php

namespace App\Application\Newsletter;

use App\Domain\Newsletter\NewsletterComposition;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Renders a composed newsletter to PDF with Dompdf and enforces the exact
 * page-count contract.
 *
 * The SAME NewsletterComposition value object used by the web preview is
 * rendered here (via the shared sheet/layout Blade partials, in PDF context),
 * so the printed document can never drift from what the studio previews.
 *
 * Validation: after each render the page count is read from the PDF canvas
 * (the same technique the reference Dompdf pipeline uses). If it does not
 * match the configured target, generation density is increased (tighter
 * typography) and the document is regenerated, up to a configured attempt
 * limit. The result reports whether the target was converged on, so callers
 * can surface an honest status.
 *
 * Dompdf is configured to keep remote resources disabled and the chroot set
 * to the Laravel public directory — only on-disk local assets (already
 * resolved by NewsletterAssetService) can be embedded.
 */
final readonly class NewsletterPdfService
{
    public function __construct(
        private NewsletterAssetService $assets,
        private array $newsletterConfig,
    ) {}

    /**
     * @param  int|null  $density  override the density step (used in tests and
     *                             forced convergence scenarios)
     */
    public function render(
        NewsletterComposition $composition,
        string $layoutSlug,
        ?int $density = null,
    ): NewsletterPdfResult {
        $pageTarget = (int) ($this->newsletterConfig['pdf']['page_target'] ?? 3);
        $attempts = (int) ($this->newsletterConfig['pdf']['regenerate_attempts'] ?? 4);

        $step = max(0, (int) $density);
        $lastCount = 0;
        $bytes = '';

        for ($attempt = 0; $attempt <= $attempts; $attempt++) {
            $html = view('newsletter.pdf', [
                'composition' => $composition,
                'layout' => $layoutSlug,
                'context' => 'pdf',
                'density' => $step,
                'masthead' => $this->assets->masthead(),
            ])->render();

            [$bytes, $lastCount] = $this->renderHtml($html);

            if ($lastCount === $pageTarget) {
                return new NewsletterPdfResult(
                    bytes: $bytes,
                    pageCount: $lastCount,
                    pageTarget: $pageTarget,
                    valid: true,
                    attempts: $attempt + 1,
                    layout: $layoutSlug,
                    message: 'PDF generated and validated to exactly '.$pageTarget.' pages.',
                );
            }

            // Tighten the layout once more and try again.
            $step++;
        }

        return new NewsletterPdfResult(
            bytes: $bytes,
            pageCount: $lastCount,
            pageTarget: $pageTarget,
            valid: false,
            attempts: $attempts + 1,
            layout: $layoutSlug,
            message: 'PDF generated but page count '.$lastCount.' could not be converged to exactly '
                .$pageTarget.' pages after '.($attempts + 1).' attempts.',
        );
    }

    /**
     * @return array{0: string, 1: int} the PDF bytes and the rendered page count
     */
    private function renderHtml(string $html): array
    {
        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->setChroot(public_path());

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pageCount = $dompdf->getCanvas()->get_page_count();

        return [$dompdf->output(), $pageCount];
    }
}
