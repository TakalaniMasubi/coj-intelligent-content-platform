<table class="ns-pagefoot" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td class="ns-pagefoot-left">
            {{ config('cojmip.newsletter.brand.name', 'City of Johannesburg') }}
            &nbsp;&middot;&nbsp;
            {{ config('cojmip.newsletter.brand.publication', 'CoJ Municipal Intelligence Newsletter') }}
        </td>
        <td class="ns-pagefoot-mid">Page {{ $page }} of {{ $total }}</td>
        <td class="ns-pagefoot-right">Edition {{ $composition->id }} &middot; Generated {{ $composition->generatedAt->format('Y-m-d H:i') }}</td>
    </tr>
</table>