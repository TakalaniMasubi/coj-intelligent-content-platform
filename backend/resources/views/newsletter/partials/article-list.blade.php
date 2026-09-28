@php($heroTop = $heroTop ?? false)
@php($heroSize = $heroSize ?? 620)
@if (count($organisation->articles) === 0)
    <div class="ns-noarticle">
        No relevant published article is currently available for this organisation.
    </div>
@else
    @php($primary = $organisation->articles[0])
    @php($secondary = $organisation->articles[1] ?? null)
    <div class="ns-article">
        @if ($heroTop)
            <div class="ns-art-hero">
                @include('newsletter.partials.media-asset', [
                    'asset' => $primary->image,
                    'context' => $context,
                    'size' => $heroSize,
                    'variant' => 'hero',
                ])
            </div>
            <p class="ns-art-kicker">
                {{ $primary->subject !== '' ? ucfirst($primary->subject) : 'Municipal article' }}
                &nbsp;&middot;&nbsp; Featured article
            </p>
            <h3 class="ns-article-title">{{ $primary->title }}</h3>
            <p class="ns-article-src">
                {{ $primary->sourceName }}
                &nbsp;|&nbsp; Source date {{ $primary->retrievedAt?->format('F j, Y') ?? 'not recorded' }}
            </p>
            @if ($primary->summary)
                <p class="ns-article-sum">{{ $primary->summary }}</p>
            @endif
        @else
            <table class="ns-art-row" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td class="ns-art-body">
                        <p class="ns-art-kicker">
                            {{ $primary->subject !== '' ? ucfirst($primary->subject) : 'Municipal article' }}
                            &nbsp;&middot;&nbsp; Featured article
                        </p>
                        <h3 class="ns-article-title">{{ $primary->title }}</h3>
                        <p class="ns-article-src">
                            {{ $primary->sourceName }}
                            &nbsp;|&nbsp; Source date {{ $primary->retrievedAt?->format('F j, Y') ?? 'not recorded' }}
                        </p>
                        @if ($primary->summary)
                            <p class="ns-article-sum">{{ $primary->summary }}</p>
                        @endif
                    </td>
                    <td class="ns-art-photo">
                        @include('newsletter.partials.media-asset', [
                            'asset' => $primary->image,
                            'context' => $context,
                            'size' => 340,
                            'variant' => 'hero',
                        ])
                    </td>
                </tr>
            </table>
        @endif

        @if ($secondary)
            <div class="ns-art-secondary">
                <span class="ns-art-second-label">Also in this edition</span>
                <span class="ns-art-second-title">{{ $secondary->title }}</span>
                <span class="ns-art-second-src">
                    {{ $secondary->sourceName }} &middot; Source date {{ $secondary->retrievedAt?->format('F j, Y') ?? 'not recorded' }}
                </span>
            </div>
        @endif

        @if ($primary->sourceUrl !== '')
            <p class="ns-article-link">Source: {{ $primary->sourceUrl }}</p>
        @endif
    </div>
@endif