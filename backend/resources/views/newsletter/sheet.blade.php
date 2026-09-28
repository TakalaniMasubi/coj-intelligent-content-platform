@php
    $densityClass = (int) $density > 0 ? ' nsd'.(int) $density : '';
    $pageOrgs = $composition->isSuccess() ? array_values($composition->organisations()) : [];
    $pageTotal = max(3, count($pageOrgs));
    $publication = (string) config('cojmip.newsletter.brand.publication', 'CoJ Municipal Intelligence Newsletter');
@endphp
<div class="nlsheet ns-layout-{{ $layout }}{{ $densityClass }}">
    {{-- PAGE 1 — masthead, this-edition introduction, Organisation 1 ------------------- --}}
    <section class="ns-page ns-page-1">
        @if ($context === 'web' && count($pageOrgs) > 0)
            <div class="ns-folio">Page 1 of {{ $pageTotal }} &middot; {{ $pageOrgs[0]->name }}</div>
        @endif

        @include('newsletter.partials.masthead', [
            'composition' => $composition,
            'masthead' => $masthead,
            'context' => $context,
        ])

        @if (count($pageOrgs) > 0)
            <div class="ns-intro">{{ $composition->introduction }}</div>

            @include('newsletter.layouts.'.$layout, [
                'organisation' => $pageOrgs[0],
                'composition' => $composition,
                'context' => $context,
                'density' => $density,
                'pageNumber' => 1,
            ])
        @else
            <div class="ns-noarticle ns-insufficient">
                {{ $composition->message }}
            </div>
        @endif

        @include('newsletter.partials.page-footer', [
            'page' => 1,
            'total' => $pageTotal,
            'composition' => $composition,
        ])
    </section>

    {{-- PAGE 2 — Organisation 2 ------------------- --}}
    @if (count($pageOrgs) > 1)
        <section class="ns-page ns-page-2 breaks">
            @if ($context === 'web')
                <div class="ns-folio">Page 2 of {{ $pageTotal }} &middot; {{ $pageOrgs[1]->name }}</div>
            @endif

            @include('newsletter.partials.running-header', [
                'publication' => $publication,
                'composition' => $composition,
            ])

            @include('newsletter.layouts.'.$layout, [
                'organisation' => $pageOrgs[1],
                'composition' => $composition,
                'context' => $context,
                'density' => $density,
                'pageNumber' => 2,
            ])

            @include('newsletter.partials.page-footer', [
                'page' => 2,
                'total' => $pageTotal,
                'composition' => $composition,
            ])
        </section>
    @endif

    {{-- PAGE 3 — Organisation 3 ------------------- --}}
    @if (count($pageOrgs) > 2)
        <section class="ns-page ns-page-3">
            @if ($context === 'web')
                <div class="ns-folio">Page 3 of {{ $pageTotal }} &middot; {{ $pageOrgs[2]->name }}</div>
            @endif

            @include('newsletter.partials.running-header', [
                'publication' => $publication,
                'composition' => $composition,
            ])

            @include('newsletter.layouts.'.$layout, [
                'organisation' => $pageOrgs[2],
                'composition' => $composition,
                'context' => $context,
                'density' => $density,
                'pageNumber' => 3,
            ])

            @include('newsletter.partials.sheet-footer', [
                'composition' => $composition,
                'masthead' => $masthead,
                'context' => $context,
            ])
        </section>
    @endif
</div>