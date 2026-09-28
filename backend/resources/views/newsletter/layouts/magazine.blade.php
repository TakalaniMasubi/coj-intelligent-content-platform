@php($featured = count($composition->organisations()))
@php($hero = 560 - (int) $density * 60)
<div class="ns-feature ns-feature-magazine">
    <p class="ns-kicker">Magazine &middot; Feature {{ $organisation->rank }} of {{ $featured }}</p>

    <h2 class="ns-orgname ns-mag-name">{{ $organisation->name }}</h2>
    <p class="ns-orgmeta">
        {{ ucfirst($organisation->type) }}
        @if ($organisation->officialWebsite)
            &nbsp;&middot;&nbsp; {{ $organisation->officialWebsite }}
        @endif
    </p>

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
            <p>{{ $organisation->description }}</p>
        </div>
    @endif

    <p class="ns-quote">
        &ldquo;Featured {{ $organisation->rank }} of {{ $featured }} in this edition for the topic
        &lsquo;{{ $composition->topic }}&rsquo;.&rdquo;
    </p>
</div>