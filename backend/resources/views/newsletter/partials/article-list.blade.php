@if (count($organisation->articles) === 0)
    <div class="ns-noarticle">
        No relevant published article is currently available in the curated repository for this organisation.
    </div>
@else
    <table class="ns-articles" cellpadding="0" cellspacing="0" role="presentation">
        @foreach ($organisation->articles as $article)
            <tr class="ns-article">
                <td>
                    <p class="ns-article-title">{{ $article->title }}</p>
                    <p class="ns-article-src">
                        {{ $article->sourceName }}
                        @if ($article->status === 'verified')
                            <span class="ns-chip ns-chip-green">Verified</span>
                        @else
                            <span class="ns-chip ns-chip-amber">Unverified</span>
                        @endif
                        @if ($article->retrievedAt)
                            {{ $article->retrievedAt->format('d M Y') }}
                        @endif
                    </p>
                    @if ($article->summary)
                        <p class="ns-article-sum">{{ $article->summary }}</p>
                    @endif
                    @if ($article->sourceUrl)
                        <span class="ns-article-link">Source: {{ $article->sourceUrl }}</span>
                    @endif
                </td>
                <td class="ns-article-photo">
                    @include('newsletter.partials.media-asset', [
                        'asset' => $article->image,
                        'context' => $context,
                        'size' => 0,
                        'variant' => 'sm',
                    ])
                </td>
            </tr>
        @endforeach
    </table>
@endif