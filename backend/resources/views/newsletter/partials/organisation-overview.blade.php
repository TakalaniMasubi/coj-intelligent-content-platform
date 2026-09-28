@php($featured = count($organisations))
<div class="nsel-strip">
    @foreach ($organisations as $n => $organisation)
        <article class="nsel-org">
            <div class="nsel-org-head">
                <span class="nsel-rank">{{ str_pad((string) $organisation->rank, 2, '0', STR_PAD_LEFT) }}</span>
                <div class="nsel-logo">
                    @include('newsletter.partials.media-asset', [
                        'asset' => $organisation->logo,
                        'context' => 'web',
                        'size' => 46,
                        'variant' => 'sm',
                    ])
                </div>
                <div class="nsel-org-title">
                    <h3>{{ $organisation->name }}</h3>
                    <p>{{ ucfirst($organisation->type) }}{{ $organisation->officialWebsite ? ' · ' . $organisation->officialWebsite : '' }}</p>
                </div>
            </div>
            @if (($organisation->mandate ?? '') !== '')
                <p class="nsel-mandate">{{ $organisation->mandate }}</p>
            @elseif (($organisation->description ?? '') !== '')
                <p class="nsel-desc">{{ $organisation->description }}</p>
            @endif
            @if (count($organisation->articles) > 0)
                <p class="nsel-art">
                    <span class="nsel-art-label">Featured article</span>
                    <span class="nsel-art-title">{{ $organisation->articles[0]->title }}</span>
                </p>
            @else
                <p class="nsel-art nsel-art-none">
                    <span class="nsel-art-label">Featured article</span>
                    <span class="nsel-art-title">No relevant published article is currently available for this organisation.</span>
                </p>
            @endif
        </article>
    @endforeach
</div>