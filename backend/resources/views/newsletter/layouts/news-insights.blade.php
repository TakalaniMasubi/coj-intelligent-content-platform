@php($featured = count($composition->organisations()))
@php($hero = 520 - (int) $density * 55)
<div class="ns-feature ns-feature-news">
    <p class="ns-kicker">News &amp; Insights &middot; Feature {{ $organisation->rank }} of {{ $featured }}</p>

    <table class="ns-org-head" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td class="ns-org-logo">
                @include('newsletter.partials.media-asset', [
                    'asset' => $organisation->logo,
                    'context' => $context,
                    'size' => 46,
                ])
            </td>
            <td class="ns-org-body">
                <h2 class="ns-orgname">{{ $organisation->name }}</h2>
                <p class="ns-orgmeta">
                    {{ ucfirst($organisation->type) }}
                    @if ($organisation->officialWebsite)
                        &nbsp;&middot;&nbsp; {{ $organisation->officialWebsite }}
                    @endif
                </p>
            </td>
        </tr>
    </table>

    @include('newsletter.partials.article-list', [
        'organisation' => $organisation,
        'context' => $context,
        'heroTop' => true,
        'heroSize' => $hero,
    ])

    @if (($organisation->mandate ?? '') !== '')
        <p class="ns-dek">{{ $organisation->mandate }}</p>
    @endif

    @if (($organisation->description ?? '') !== '')
        <div class="ns-body">
            <p>About {{ $organisation->name }} &mdash; {{ $organisation->description }}</p>
        </div>
    @endif
</div>