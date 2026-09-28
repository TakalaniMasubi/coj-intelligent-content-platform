<div class="ns-masthead">
    <table class="ns-mast-top" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td class="ns-mast-logo">
                @include('newsletter.partials.media-asset', [
                    'asset' => $masthead['brand'],
                    'context' => $context,
                    'size' => $context === 'web' ? 180 : 158,
                ])
            </td>
            <td class="ns-mast-main">
                <p class="ns-eyebrow">{{ config('cojmip.newsletter.brand.eyebrow', 'City of Johannesburg — Municipal Insights') }}</p>
                <h1 class="ns-title">{{ $composition->title }}</h1>
                <p class="ns-subtitle">{{ $composition->subtitle }}</p>
                <p class="ns-date">{{ $composition->date }} &nbsp;|&nbsp; Edition {{ $composition->id }}</p>
            </td>
            <td class="ns-mast-seal">
                @include('newsletter.partials.media-asset', [
                    'asset' => $masthead['seal'],
                    'context' => $context,
                    'size' => $context === 'web' ? 52 : 48,
                ])
            </td>
        </tr>
    </table>
</div>