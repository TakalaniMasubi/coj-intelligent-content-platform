@php($featured = count($composition->organisations()))
<div class="ns-feature ns-feature-spotlight">
    <p class="ns-kicker">Municipal Spotlight &middot; Profile {{ $organisation->rank }} of {{ $featured }}</p>

    <div class="ns-spotlight-head">
        <div class="ns-spotlight-logo">
            @include('newsletter.partials.media-asset', [
                'asset' => $organisation->logo,
                'context' => $context,
                'size' => 84,
            ])
        </div>
        <h2 class="ns-orgname">{{ $organisation->name }}</h2>
        <p class="ns-orgmeta">
            {{ ucfirst($organisation->type) }} of the City of Johannesburg
            @if ($organisation->officialWebsite)
                &nbsp;&middot;&nbsp; {{ $organisation->officialWebsite }}
            @endif
        </p>
    </div>

    @if (($organisation->mandate ?? '') !== '')
        <p class="ns-dek ns-dek-center">{{ $organisation->mandate }}</p>
    @endif

    @if (($organisation->description ?? '') !== '')
        <div class="ns-body">
            <p>{{ $organisation->description }}</p>
        </div>
    @endif

    @include('newsletter.partials.article-list', [
        'organisation' => $organisation,
        'context' => $context,
    ])
</div>