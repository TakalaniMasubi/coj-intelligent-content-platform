<div class="ns-feature">
    <p class="ns-kicker">News &amp; Insights — Insight {{ $organisation->rank }}</p>
    <p class="ns-meta" style="margin:0 0 0.55em;">
        Curated reading for
        <strong>{{ $organisation->name }}</strong>
        &nbsp;·&nbsp; {{ ucfirst($organisation->type) }}
        &nbsp;·&nbsp;
        @include('newsletter.partials.verification-chip', [
            'verification' => $organisation->verification,
        ])
    </p>
    <p class="ns-meta" style="margin:0 0 0.75em;">
        Repository articles matched on this edition's topic, ordered by relevance
        (verified sources preferred, newest retrieved first).
    </p>
    @include('newsletter.partials.article-list', [
        'organisation' => $organisation,
        'context' => $context,
    ])
    @if ($organisation->description)
        <div class="ns-body" style="margin-top:0.6em;">
            <p>{{ $organisation->description }}</p>
        </div>
    @endif
    @if ($organisation->reasons !== [])
        <div class="ns-why">
            <strong>Why included</strong>
            <ul>
                @foreach ($organisation->reasons as $reason)
                    <li>{{ $reason }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>