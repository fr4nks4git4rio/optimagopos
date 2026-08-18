<table>
    <thead>
        <tr>
            <td colspan="16" style="font-weight: 700; font-size: 16px">{{ $name }}</td>
        </tr>
        <tr></tr>
        @if()
        <tr></tr>
        <tr>
            <th>{{ __('site.invoices.index_storage.date') }}</th>
            <th>{{ __('site.invoices.index_storage.f_int') }}</th>
            <th>{{ __('site.invoices.index_storage.type') }}</th>
            <th>{{ __('site.invoices.index_storage.issuer') }}</th>
            <th>{{ __('site.invoices.index_storage.issuer_rfc') }}</th>
            <th>{{ __('site.invoices.index_storage.receiver') }}</th>
            <th>{{ __('site.invoices.index_storage.receiver_rfc') }}</th>
            <th>{{ __('site.invoices.index_storage.uuid') }}</th>
            <th>{{ __('site.invoices.index_storage.status') }}</th>
            <th>{{ __('site.invoices.index_storage.concepts') }}</th>
            <th style="width: 90px;">{{ __('site.invoices.index_storage.currency') }}</th>
            <th>{{ __('site.invoices.index_storage.subtotal') }}</th>
            <th>{{ __('site.invoices.index_storage.iva') }}</th>
            <th>{{ __('site.invoices.index_storage.total') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($facturas as $factura)
            <tr>
                <td>{{ $factura->fecha_emision_str }}</td>
                <td>
                    {{ $factura->folio_interno }}
                </td>
                <td style="text-align: center">
                    <span>{{ $factura->tipo }}</span>
                </td>
                <td>{{ $factura->emisor }}</td>
                <td>{{ $factura->rfc_emisor }}</td>
                <td>{{ $factura->receptor }}</td>
                <td>{{ $factura->rfc_receptor }}</td>
                <td>{{ $factura->uuid }}</td>
                <td style="text-align: center">
                    <span>{{ __('site.statuses.invoices.' . $factura->estado) }}</span>
                </td>
                <td>{{ $factura->conceptos }}</td>
                <td style="text-align: center">{{ $factura->moneda }}</td>
                <td>${{ number_format(max($factura->subtotal, 0), 2) }}</td>
                <td>${{ number_format(max($factura->iva, 0), 2) }}</td>
                <td>${{ number_format(max($factura->total, 0), 2) }}</td>
            </tr>
            @if ($factura->estado == 'CANCELADA')
                <tr>
                    <td colspan="17"
                        style="background-color: #fff; padding-top: 2px; padding-bottom: 2px; text-align: left;">
                        <p>{{ __('site.invoices.index_storage.cancellation_motive') }}:&nbsp;
                            {{ $factura->motivo_cancelacion }}</p>
                    </td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>
