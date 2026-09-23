<div class="ns-section-title">This Edition — Top {{ count($organisations) }} Organisations</div>
<table class="ns-overview" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        @foreach ($organisations as $organisation)
            <td class="ns-card">
                <div class="ns-card-top">
                    <span class="ns-rank">{{ str_pad((string) $organisation->rank, 2, '0', STR_PAD_LEFT) }}</span>
                    &nbsp;<span class="ns-chip ns-chip-gold">Score {{ $organisation->score }}</span>
                </div>
                <div style="text-align:center;margin-bottom:0.55em;">
                    @include('newsletter.partials.media-asset', [
                        'asset' => $organisation->logo,
                        'context' => $context,
                        'size' => 44,
                    ])
                </div>
                <div class="ns-card-org" style="text-align:center;">{{ $organisation->name }}</div>
                <div class="ns-card-type" style="text-align:center;">
                    {{ ucfirst($organisation->type) }}
                    <br>
                    @include('newsletter.partials.verification-chip', [
                        'verification' => $organisation->verification,
                    ])
                </div>
            </td>
        @endforeach
    </tr>
</table>