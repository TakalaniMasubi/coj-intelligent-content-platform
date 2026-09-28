@php($featured = count($composition->organisations()))
<div class="ns-feature ns-feature-executive">
    <p class="ns-kicker">Executive Brief &middot; Feature {{ $organisation->rank }} of {{ $featured }}</p>

    <table class="ns-org-head" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td class="ns-org-logo">
                @include('newsletter.partials.media-asset', [
                    'asset' => $organisation->logo,
                    'context' => $context,
                    'size' => 54,
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

    @if (($organisation->mandate ?? '') !== '')
        <p class="ns-dek">{{ $organisation->mandate }}</p>
    @endif

    @if (($organisation->description ?? '') !== '')
        <div class="ns-body">
            <p>{{ $organisation->description }}</p>
        </div>
    @endif

    <p class="ns-facts">
        <strong>At a glance</strong>
        &nbsp;|&nbsp; {{ ucfirst($organisation->type) }}
        &nbsp;|&nbsp; Featured {{ $organisation->rank }} of {{ $featured }} in this edition
        @if ($organisation->officialWebsite)
            &nbsp;|&nbsp; {{ $organisation->officialWebsite }}
        @endif
    </p>

    @include('newsletter.partials.article-list', [
        'organisation' => $organisation,
        'context' => $context,
    ])
</div>