@php($densityClass = (int) $density > 0 ? ' nsd'.(int) $density : '')
<div class="nlsheet{{ $densityClass }}">
    <section class="ns-page ns-page-1">
        @include('newsletter.partials.masthead', [
            'composition' => $composition,
            'masthead' => $masthead,
            'context' => $context,
        ])
        <div class="ns-intro">{{ $composition->introduction }}</div>

        @if ($composition->isSuccess() && count($composition->organisations()) > 0)
            @include('newsletter.partials.organisation-overview', [
                'organisations' => $composition->organisations(),
                'context' => $context,
            ])
        @else
            <div class="ns-noarticle" style="font-size:0.88em;padding:1em 1.1em;">
                {{ $composition->message }}
            </div>
        @endif

        @include('newsletter.partials.page-footer', ['page' => 1, 'total' => 3, 'composition' => $composition])
    </section>

    @if ($composition->isSuccess() && count($composition->organisations()) > 0)
        @php($featured = $composition->organisations())
        @php($onPageTwo = array_slice($featured, 0, 2))
        @php($onPageThree = array_slice($featured, 2))

        <section class="ns-page ns-page-2 breaks">
            @foreach ($onPageTwo as $organisation)
                @include('newsletter.layouts.'.$layout, [
                    'organisation' => $organisation,
                    'composition' => $composition,
                    'context' => $context,
                    'density' => $density,
                ])
            @endforeach
            @include('newsletter.partials.page-footer', ['page' => 2, 'total' => 3, 'composition' => $composition])
        </section>

        @if ($onPageThree !== [])
            <section class="ns-page ns-page-3 breaks">
                @foreach ($onPageThree as $organisation)
                    @include('newsletter.layouts.'.$layout, [
                        'organisation' => $organisation,
                        'composition' => $composition,
                        'context' => $context,
                        'density' => $density,
                    ])
                @endforeach
                @include('newsletter.partials.sheet-footer', [
                    'composition' => $composition,
                    'masthead' => $masthead,
                    'context' => $context,
                ])
            </section>
        @endif
    @endif
</div>