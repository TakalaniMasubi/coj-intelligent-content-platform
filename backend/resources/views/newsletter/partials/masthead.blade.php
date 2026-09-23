<div class="ns-masthead">
    <div class="ns-mast-logo">
        @include('newsletter.partials.media-asset', [
            'asset' => $masthead['brand'],
            'context' => $context,
            'size' => $context === 'web' ? 236 : 214,
        ])
    </div>
    <p class="ns-eyebrow">{{ config('cojmip.newsletter.brand.eyebrow', 'City of Johannesburg — Municipal Insights') }}</p>
    <h1 class="ns-title">{{ $composition->title }}</h1>
    <p class="ns-subtitle">{{ $composition->subtitle }}</p>
    <p class="ns-date">{{ $composition->date }} &nbsp;|&nbsp; Edition {{ $composition->id }}</p>
</div>