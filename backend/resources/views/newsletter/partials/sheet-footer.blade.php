<div class="ns-seal-row">
    @include('newsletter.partials.media-asset', [
        'asset' => $masthead['seal'],
        'context' => $context,
        'size' => $context === 'web' ? 40 : 34,
    ])
</div>
<div class="ns-footer">
    {{ $composition->footnote }}
    <br>
    {{ config('cojmip.newsletter.brand.name', 'City of Johannesburg') }} &nbsp;|&nbsp;
    {{ config('cojmip.newsletter.brand.publication', 'CoJ Municipal Intelligence Newsletter') }} &nbsp;|&nbsp;
    Edition {{ $composition->id }}
    <br>
    Generated {{ $composition->generatedAt->format('Y-m-d H:i') }} — page 3 of 3
</div>