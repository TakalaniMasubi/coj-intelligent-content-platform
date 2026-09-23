<?php

namespace App\Application\Newsletter;

use App\Domain\Article\Article;
use App\Domain\Newsletter\NewsletterMediaAsset;
use App\Domain\Organisation\CoJOrganisation;
use App\Domain\Organisation\OrganisationType;

/**
 * Resolves every image/logo the newsletter needs, safely, for BOTH the web
 * preview and the Dompdf PDF.
 *
 * For each asset a single NewsletterMediaAsset is produced that carries a
 * browser web URL and the absolute-on-disk path required by Dompdf, plus the
 * real pixel dimensions so renderers preserve the aspect ratio and never
 * distort the logo. When no real image exists for an organisation or an
 * article, a professional, deterministic FALLBACK is used instead:
 *
 *   - organisations: a styled monogram (initials in a brand-coloured chip)
 *     labelled as a repository placeholder — never a broken image
 *   - articles: a branded colour panel labelled with the article subject
 *
 * The four presentation kinds (logo, seal, featured image, fallback panel)
 * are explicitly typed so layouts can adapt their treatment.
 */
final class NewsletterAssetService
{
    /** Deterministic fallback palette (organisation type -> chip colour). */
    private const TYPE_FILLS = [
        'entity' => '#0f5c3a',
        'directorate' => '#1d4e89',
        'office' => '#7a3f92',
    ];

    /** Deterministic fallback palette for article subject panels. */
    private const PANEL_FILLS = [
        '#0f5c3a',
        '#1d4e89',
        '#9a6b00',
        '#b03a2e',
        '#2e6b66',
        '#7a3f92',
    ];

    /** @var array<int, int> cached image dimension lookups by absolute path */
    private array $dimensionCache = [];

    public function __construct(
        private array $newsletterConfig,
    ) {}

    /**
     * Publication branding assets (masthead logo + square brand seal).
     *
     * @return array<string, NewsletterMediaAsset>
     */
    public function masthead(): array
    {
        $brand = $this->fromFile(
            (string) ($this->newsletterConfig['brand']['logo'] ?? ''),
            NewsletterMediaAsset::KIND_LOGO,
            'City of Johannesburg',
        );

        $seal = $this->fromFile(
            (string) ($this->newsletterConfig['brand']['seal'] ?? ''),
            NewsletterMediaAsset::KIND_SEAL,
            'CoJ',
        );

        return [
            'brand' => $brand,
            'seal' => $seal,
        ];
    }

    /**
     * Organisation logo: a real logo file when present in the curated media
     * directory, otherwise a deterministic monogram fallback.
     */
    public function organisationLogo(CoJOrganisation $organisation): NewsletterMediaAsset
    {
        $dir = $this->mediaDirectory('organisation_logos_dir');
        $asset = $dir !== ''
            ? $this->resolveInDir($dir, $organisation->id, NewsletterMediaAsset::KIND_LOGO, $organisation->name)
            : null;

        if ($asset !== null) {
            return $asset;
        }

        $fill = self::TYPE_FILLS[$organisation->organisationType->value]
            ?? self::TYPE_FILLS[OrganisationType::Entity->value];

        return new NewsletterMediaAsset(
            kind: NewsletterMediaAsset::KIND_LOGO,
            source: NewsletterMediaAsset::SOURCE_MONOGRAM,
            label: $organisation->name,
            initials: $this->initials($organisation->name, $organisation->abbreviation),
            fill: $fill,
            width: 0,
            height: 0,
        );
    }

    /**
     * Article media: a real featured image when present in the curated media
     * directory, otherwise a branded subject-labelled panel fallback.
     */
    public function articleImage(Article $article, string $subject): NewsletterMediaAsset
    {
        $dir = $this->mediaDirectory('article_images_dir');
        $asset = $dir !== ''
            ? $this->resolveInDir($dir, $article->id, NewsletterMediaAsset::KIND_FEATURED_IMAGE, $article->title)
            : null;

        if ($asset !== null) {
            return $asset;
        }

        $subjectLabel = $subject !== '' ? $subject : 'Repository Article';
        $fill = self::PANEL_FILLS[(crc32(strtolower($subjectLabel)) % count(self::PANEL_FILLS))];

        return new NewsletterMediaAsset(
            kind: NewsletterMediaAsset::KIND_FEATURED_IMAGE,
            source: NewsletterMediaAsset::SOURCE_PANEL,
            label: ucfirst($subjectLabel),
            initials: '',
            fill: $fill,
            width: 0,
            height: 0,
        );
    }

    /**
     * Build a file-based asset from a public-relative path.
     */
    private function fromFile(string $relativeWeb, string $kind, string $label): NewsletterMediaAsset
    {
        $absolute = realpath(public_path($relativeWeb));

        if ($absolute === false || ! is_file($absolute)) {
            return new NewsletterMediaAsset(
                kind: $kind,
                source: NewsletterMediaAsset::SOURCE_PANEL,
                label: $label,
                fill: self::TYPE_FILLS['directorate'],
                width: 0,
                height: 0,
            );
        }

        [$width, $height] = $this->dimensions($absolute);

        return new NewsletterMediaAsset(
            kind: $kind,
            source: NewsletterMediaAsset::SOURCE_FILE,
            label: $label,
            webUrl: asset($relativeWeb),
            pdfPath: $this->forwardSlashes($absolute),
            width: $width,
            height: $height,
        );
    }

    /**
     * Resolve `{stem}.{png|jpg|jpeg}` inside a public media directory.
     */
    private function resolveInDir(string $dir, string $stem, string $kind, string $label): ?NewsletterMediaAsset
    {
        foreach (['png', 'jpg', 'jpeg'] as $extension) {
            $relative = $dir.'/'.$stem.'.'.$extension;
            $absolute = realpath(public_path($relative));

            if ($absolute !== false && is_file($absolute)) {
                [$width, $height] = $this->dimensions($absolute);

                return new NewsletterMediaAsset(
                    kind: $kind,
                    source: NewsletterMediaAsset::SOURCE_FILE,
                    label: $label,
                    webUrl: asset($relative),
                    pdfPath: $this->forwardSlashes($absolute),
                    width: $width,
                    height: $height,
                );
            }
        }

        return null;
    }

    private function mediaDirectory(string $key): string
    {
        $relative = (string) ($this->newsletterConfig['media'][$key] ?? '');

        if ($relative === '') {
            return '';
        }

        return trim($relative, '/\\');
    }

    /** @return array{int, int} */
    private function dimensions(string $absolute): array
    {
        if (! isset($this->dimensionCache[$absolute])) {
            $info = @getimagesize($absolute);

            $this->dimensionCache[$absolute] = is_array($info) && isset($info[0], $info[1])
                ? [(int) $info[0], (int) $info[1]]
                : [0, 0];
        }

        return $this->dimensionCache[$absolute];
    }

    private function forwardSlashes(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    private function initials(string $name, ?string $abbreviation): string
    {
        $words = preg_split('/\s+/', trim((string) $abbreviation));

        if (is_array($words) && $words !== ['']) {
            return strtoupper(mb_substr(implode('', $words), 0, 3));
        }

        $skip = ['and', 'the', 'of', 'in', 'for', '&'];
        $letters = [];
        foreach (preg_split('/\s+/', trim($name)) ?: [] as $word) {
            $clean = strtolower(preg_replace('/[^a-z0-9]/i', '', $word));
            if ($clean === '' || in_array($clean, $skip, true)) {
                continue;
            }
            $letters[] = mb_strtoupper(mb_substr($clean, 0, 1));
            if (count($letters) >= 2) {
                break;
            }
        }

        $result = implode('', $letters);
        if (mb_strlen($result) < 1) {
            $result = mb_strtoupper(mb_substr(trim($name), 0, 1));
        }

        return mb_substr($result, 0, 3);
    }
}
