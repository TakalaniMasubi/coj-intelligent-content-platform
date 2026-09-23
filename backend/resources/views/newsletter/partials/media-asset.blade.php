@php
    $assetW = (int) $size;
    $assetH = 0;
    if ($assetW > 0 && $asset->aspectRatio() > 0) {
        $assetH = (int) round($assetW / $asset->aspectRatio());
    }
    $variant = $variant ?? 'panel';
@endphp
@if ($asset->isImage() && $asset->path($context) !== '')
    <img
        src="{{ $asset->path($context) }}"
        width="{{ $assetW }}"
        height="{{ $assetH }}"
        alt="{{ $asset->label }}"
        {{ $context === 'web' ? 'style="object-fit:contain;max-width:100%;"' : '' }}
        class="{{ $context === 'web' ? 'ns-logo-img' : 'ns-seal-img' }}"
    >
@elseif ($asset->source === 'monogram')
    <span class="ns-monogram" style="background:{{ $asset->fill }};" role="img" aria-label="{{ $asset->label }}">{{ $asset->initials }}</span>
@else
    <span class="{{ $variant === 'sm' ? 'ns-panel-sm' : 'ns-panel' }}" style="background:{{ $asset->fill }};" role="img" aria-label="{{ $asset->label }}">{{ $asset->label }}</span>
@endif