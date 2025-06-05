<table>
    <thead>
        <tr>
            <td colspan="16" style="font-weight: 700; font-size: 16px">Facturas Emitidas</td>
        </tr>
        <tr></tr>
        <tr>
            <th>Fecha de Emisión</th>
            <th>F. Int.</th>
            <th>Emisor</th>
            <th>RFC Emisor</th>
            <th>Receptor</th>
            <th>RFC Receptor</th>
            <th>UUID</th>
            <th>Estado</th>
            <th>Conceptos</th>
            <th style="width: 90px;">Moneda</th>
            <th>Subtotal</th>
            <th>IVA</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($facturas as $factura)
        <tr>
            <td>{{$factura->fecha_emision_str}}</td>
            <td>
                {{$factura->folio_interno}}
            </td>
            <td style="text-align: center">
                <span>{{$factura->tipo}}</span>
            </td>
            <td>{{\Illuminate\Support\Facades\Crypt::decrypt($factura->emisor)}}</td>
            <td>{{\Illuminate\Support\Facades\Crypt::decrypt($factura->rfc_emisor)}}</td>
            <td>{{\Illuminate\Support\Facades\Crypt::decrypt($factura->receptor)}}</td>
            <td>{{\Illuminate\Support\Facades\Crypt::decrypt($factura->rfc_receptor)}}</td>
            <td>{{$factura->uuid}}</td>
            <td style="text-align: center">
                <span>{{ $factura->estado }}</span>
            </td>
            <td>{{$factura->conceptos}}</td>
            <td style="text-align: center">{{$factura->moneda}}</td>
            <td>${{number_format(max($factura->subtotal, 0), 2)}}</td>
            <td>${{number_format(max($factura->iva, 0), 2)}}</td>
            <td>${{number_format(max($factura->total, 0), 2)}}</td>
        </tr>
        @if($factura->estado == 'CANCELADA')
        <tr>
            <td colspan="17" style="background-color: #fff; padding-top: 2px; padding-bottom: 2px; text-align: left;">
                <p>Motivo de Cancelación:&nbsp; {{ $factura->motivo_cancelacion }}</p>
            </td>
        </tr>
        @endif
        @endforeach
    </tbody>
</table>
