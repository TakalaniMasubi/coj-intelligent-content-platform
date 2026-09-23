<div class="ns-feature">
    <p class="ns-kicker">Executive Brief — Feature {{ $organisation->rank }}</p>
    <table class="ns-feature-head" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td class="ns-fh-logo">
                @include('newsletter.partials.media-asset', [
                    'asset' => $organisation->logo,
                    'context' => $context,
                    'size' => 40,
                ])
            </td>
            <td class="ns-fh-body">
                <h2 class="ns-orgname">{{ $organisation->name }}</h2>
                <p class="ns-meta">
                    {{ ucfirst($organisation->type) }}
                    &nbsp;·&nbsp;
                    @include('newsletter.partials.verification-chip', [
                        'verification' => $organisation->verification,
                    ])
                    &nbsp;·&nbsp;
                    <span class="ns-chip ns-chip-gold">Score {{ $organisation->score }}</span>
                </p>
            </td>
        </tr>
    </table>
    <div class="ns-dek">At a glance — {{ $organisation->name }}.</div>
    @if ($organisation->description)
        <div class="ns-body">
            <p>{{ $organisation->description }}</p>
        </div>
    @endif
    <div class="ns-facts">
        <strong>At a glance</strong>
        &nbsp;|&nbsp; {{ ucfirst($organisation->type) }}
        &nbsp;|&nbsp; {{ ucfirst($organisation->verification) }}
        &nbsp;|&nbsp; Ranked {{ $organisation->rank }}
        @if ($organisation->officialWebsite)
            &nbsp;|&nbsp; {{ $organisation->officialWebsite }}
        @endif
    </div>
    @include('newsletter.partials.article-list', [
        'organisation' => $organisation,
        'context' => $context,
    ])
</div>