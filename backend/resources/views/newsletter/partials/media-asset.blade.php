@php
    $assetW = (int) $size;
    $assetH = 0;
    if ($assetW > 0 && $asset->aspectRatio() > 0) {
        $assetH = (int) round($assetW / $asset->aspectRatio());
    }
    $assetVariant = $variant ?? 'panel';
@endphp
@if ($asset->isImage() && $asset->path($context) !== '')
    <img
        src="{{ $asset->path($context) }}"
        width="{{ $assetW }}"
        @if ($assetH > 0)
            height="{{ $assetH }}"
        @endif
        alt="{{ $asset->label }}"
        class="{{ $assetVariant === 'hero' ? 'ns-img-hero' : ($context === 'web' ? 'ns-logo-img' : 'ns-seal-img') }}"
        @if ($context === 'web')
            style="object-fit:contain;max-width:100%;"
        @endif
    >
@elseif ($asset->source === 'monogram')
    <span class="{{ $assetVariant === 'hero' ? 'ns-monogram-hero' : 'ns-monogram' }}"
          style="background:{{ $asset->fill }};" role="img" aria-label="{{ $asset->label }}">
        {{ $asset->initials }}
    </span>
@else
    <span class="{{ $assetVariant === 'sm' ? 'ns-panel-sm' : ($assetVariant === 'hero' ? 'ns-panel-hero' : 'ns-panel') }}"
          style="background:{{ $asset->fill }};" role="img" aria-label="{{ $asset->label }}">
        {{ $asset->label }}
    </span>
@endif