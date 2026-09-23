<div class="ns-feature">
    <p class="ns-kicker">Municipal Spotlight — Profile {{ $organisation->rank }}</p>
    <table class="ns-feature-head" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td class="ns-fh-logo" style="width:5.6em;">
                @include('newsletter.partials.media-asset', [
                    'asset' => $organisation->logo,
                    'context' => $context,
                    'size' => 52,
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
    <div class="ns-dek">
        {{ ucfirst($organisation->type) }} within the City of Johannesburg's curated repository.
    </div>
    <div class="ns-body">
        @if ($organisation->description)
            <p>{{ $organisation->description }}</p>
        @else
            <p>No description is currently available in the curated repository for this organisation.</p>
        @endif
    </div>
    @if ($organisation->officialWebsite)
        <div class="ns-facts">
            <strong>Official source</strong>
            &nbsp;|&nbsp; {{ $organisation->officialWebsite }}
        </div>
    @endif
    @if ($organisation->reasons !== [])
        <div class="ns-why">
            <strong>Why selected for this topic</strong>
            <ul>
                @foreach ($organisation->reasons as $reason)
                    <li>{{ $reason }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @include('newsletter.partials.article-list', [
        'organisation' => $organisation,
        'context' => $context,
    ])
</div>