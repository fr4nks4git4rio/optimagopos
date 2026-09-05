<table>
    <thead>
        <tr>
            <td colspan="6" style="font-weight: bold; font-size: 16px; text-align: center;">{{ $name }}</td>
        </tr>
        <tr></tr>
        <tr>
            <td colspan="6">
                {{ __('site.common.period') }}:&nbsp;{{ $fechaInicio ?: '-' }} al {{ $fechaFin ?: '-' }}
                @if (!empty($sucursalesSeleccionadas))
                    &nbsp;|&nbsp;
                    {{ __('site.reports.sales_by_operator.branches') }}: &nbsp;
                    {{ Illuminate\Support\Str::replaceLast(', ', ' ' . __('site.common.and') . ' ', implode(', ', $sucursalesSeleccionadas)) }}
                @endif
            </td>
        </tr>
        <tr></tr>
        <tr>
            @foreach ($sorts as $sort)
                <th style="text-align: center; vertical-align: middle; white-space: nowrap !important">
                    {{ $sort }}
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse($records as $sucursal_id => $sucursalData)
            @foreach ($sucursalData['movimientos'] as $record)
                <tr>
                    @if ($loop->first)
                        <td style="text-align: center; vertical-align: middle;"
                            rowspan="{{ count($sucursalData['movimientos']) }}">
                            {{ $sucursalData['sucursal'] }}
                        </td>
                    @endif
                    <td style="text-align: center;">{{ $record->fecha_str }}</td>
                    <td style="text-align: center;">{{ $record->movimiento }}</td>
                    <td style="text-align: center;">{{ $record->forma_pago }}</td>
                    <td style="text-align: center;">{{ $record->creado_por }}</td>
                    <td style="text-align: right;">{{ number_format($record->monto, 2) }}</td>
                </tr>
            @endforeach

            {{-- Totalizador por sucursal --}}
            <tr>
                <td colspan="5" style="font-weight: bold !important; text-align: right;">
                    {{ __('site.reports.cash_movements.total') }} {{ $sucursalData['sucursal'] }}</td>
                <td style="font-weight: bold !important; text-align: right;">
                    {{ number_format($sucursalData['total'], 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6">
                    {{ __('site.common.results_not_found') }}...
                </td>
            </tr>
        @endforelse
    </tbody>
    @if (count($records) > 0)
        <tfoot>
            <tr>
                <td colspan="5" style="text-align: right; font-weight: bold;">
                    {{ __('site.reports.cash_movements.grand_total') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($grandTotal, 2) }}
                </td>
            </tr>
        </tfoot>
    @endif
</table>
