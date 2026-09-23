<?php

namespace App\Domain\Newsletter;

/**
 * A resolved media asset for a newsletter.
 *
 * Carries both a browser-safe web URL and an absolute file path for Dompdf,
 * so the SAME selected asset renders safely in the web preview and in the
 * PDF (the PDF path is only meaningful inside the Dompdf chroot).
 *
 * Asset types are explicitly distinguished so presentation can adapt:
 *   - logo           an organisation or publication logo mark
 *   - seal           a square brand seal (e.g. favicon-style mark)
 *   - featured_image a photographic / illustrative asset for an article
 *
 * Fallbacks are honest: when no real file exists, `source` is 'monogram'
 * (styled initials) or 'panel' (branded colour panel) and `isFallback()` is
 * true. Width/height reflect the actual image dimensions so renderers can
 * preserve the aspect ratio and never distort the asset.
 */
final readonly class NewsletterMediaAsset
{
    public const KIND_LOGO = 'logo';

    public const KIND_SEAL = 'seal';

    public const KIND_FEATURED_IMAGE = 'featured_image';

    public const SOURCE_FILE = 'file';

    public const SOURCE_MONOGRAM = 'monogram';

    public const SOURCE_PANEL = 'panel';

    public function __construct(
        public string $kind,
        public string $source,
        public string $label,
        public string $initials = '',
        public string $fill = '',
        public string $webUrl = '',
        public string $pdfPath = '',
        public int $width = 0,
        public int $height = 0,
    ) {}

    public function isImage(): bool
    {
        return $this->source === self::SOURCE_FILE;
    }

    public function isFallback(): bool
    {
        return ! $this->isImage();
    }

    /**
     * Wide-to-tall aspect ratio based on the real image dimensions. Zero for
     * fallbacks, where no raster distortion can occur.
     */
    public function aspectRatio(): float
    {
        return $this->height > 0 ? $this->width / $this->height : 0.0;
    }

    /**
     * Resolve the path to use for a given render context.
     *
     * @param  'web'|'pdf'  $context
     */
    public function path(string $context = 'web'): string
    {
        if ($context === 'pdf' && $this->pdfPath !== '') {
            return $this->pdfPath;
        }

        return $this->webUrl;
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'source' => $this->source,
            'label' => $this->label,
            'initials' => $this->initials,
            'fill' => $this->fill,
            'web_url' => $this->webUrl,
            'pdf_path' => $this->pdfPath,
            'width' => $this->width,
            'height' => $this->height,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            kind: (string) ($data['kind'] ?? self::KIND_LOGO),
            source: (string) ($data['source'] ?? self::SOURCE_MONOGRAM),
            label: (string) ($data['label'] ?? ''),
            initials: (string) ($data['initials'] ?? ''),
            fill: (string) ($data['fill'] ?? ''),
            webUrl: (string) ($data['web_url'] ?? ''),
            pdfPath: (string) ($data['pdf_path'] ?? ''),
            width: (int) ($data['width'] ?? 0),
            height: (int) ($data['height'] ?? 0),
        );
    }
}
