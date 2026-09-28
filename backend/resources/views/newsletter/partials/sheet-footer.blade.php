<table class="ns-pagefoot" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td class="ns-pagefoot-left">
            {{ config('cojmip.newsletter.brand.name', 'City of Johannesburg') }}
            &nbsp;&middot;&nbsp;
            Edition {{ $composition->id }}
        </td>
        <td class="ns-pagefoot-mid">Page 3 of 3</td>
        <td class="ns-pagefoot-right">Generated {{ $composition->generatedAt->format('Y-m-d H:i') }}</td>
    </tr>
</table>
<div class="ns-colophon">
    <div class="ns-seal-row">
        @include('newsletter.partials.media-asset', [
            'asset' => $masthead['seal'],
            'context' => $context,
            'size' => $context === 'web' ? 40 : 34,
        ])
    </div>
    <p class="ns-colophon-notes">{{ $composition->footnote }}</p>
    <p class="ns-colophon-src">
        Every organisation and article in this edition is real curated repository data with source
        attribution and verification status recorded for auditability. No content is invented and
        no AI is used to generate this newsletter.
    </p>
</div>