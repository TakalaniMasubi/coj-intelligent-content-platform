<div class="ns-feature">
    <p class="ns-kicker">Magazine — Feature {{ $organisation->rank }}</p>
    <table class="ns-feature-head" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td style="width:8em;vertical-align:middle;padding-right:0.9em;">
                @include('newsletter.partials.media-asset', [
                    'asset' => $organisation->logo,
                    'context' => $context,
                    'size' => 64,
                ])
            </td>
            <td style="vertical-align:middle;">
                <h2 class="ns-orgname" style="font-size:1.5em;">{{ $organisation->name }}</h2>
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
    <div class="ns-dek">The story behind the selection.</div>
    <div class="ns-body">
        @if ($organisation->description)
            <p>{{ $organisation->description }}</p>
        @else
            <p>No description is currently available in the curated repository for this organisation.</p>
        @endif
    </div>
    <div class="ns-quote">
        &ldquo;Ranked {{ $organisation->rank }} of {{ $composition->available }} eligible
        organisations for the topic “{{ $composition->topic }}” in the curated repository.&rdquo;
    </div>
    @if ($organisation->reasons !== [])
        <div class="ns-why">
            <strong>Editor's note — why this organisation</strong>
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