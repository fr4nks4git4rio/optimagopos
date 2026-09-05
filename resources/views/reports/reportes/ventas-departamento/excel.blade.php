<table>
    <thead>
        <tr>
            <td colspan="4" style="font-weight: bold; font-size: 16px; text-align: center;">{{ $name }}</td>
        </tr>
        <tr></tr>
        <tr>
            <td colspan="4">
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
                <th rowspan="2" style="text-align: center; vertical-align: middle; white-space: nowrap !important">
                    {{ $sort }}
                </th>
            @endforeach
            <th colspan="2" style="text-align:center; white-space: nowrap !important">
                {{ __('site.reports.sales_by_department.sales') }}</th>
        </tr>
        <tr>
            <th style="white-space: nowrap !important; text-align: right;">
                {{ __('site.reports.sales_by_department.amount') }}</th>
            <th style="white-space: nowrap !important; text-align: center;">
                {{ __('site.reports.sales_by_department.quantity') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $sucursal_id => $sucursalData)
            @foreach ($sucursalData['departamentos'] as $record)
                <tr>
                    @if ($loop->first)
                        <td style="text-align: center; vertical-align: middle;"
                            rowspan="{{ count($sucursalData['departamentos']) + 1 }}">
                            {{ $sucursalData['sucursal'] }}
                        </td>
                    @endif
                    <td style="text-align: center;">{{ $record->nombre }}</td>
                    <td style="text-align: right;">{{ number_format($record->ventas_importe, 2) }}</td>
                    <td style="text-align: center;">{{ $record->ventas_cant }}</td>
                </tr>
            @endforeach

            {{-- Totalizador por sucursal --}}
            <tr>
                <td style="font-weight: bold !important; text-align: right;">
                    {{ __('site.reports.sales_by_department.total') }} {{ $sucursalData['sucursal'] }}</td>
                <td style="font-weight: bold !important; text-align: right;">
                    {{ number_format($sucursalData['totales']['ventas_importe'], 2) }}</td>
                <td style="font-weight: bold !important; text-align: center;">
                    {{ $sucursalData['totales']['ventas_cant'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">
                    {{ __('site.common.results_not_found') }}...
                </td>
            </tr>
        @endforelse
    </tbody>
    @if (count($records) > 0)
        <tfoot>
            <tr>
                <td colspan="2" style="text-align: right; font-weight: bold;">{{ __('site.reports.sales_by_department.grand_total') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($grandTotal['ventas_importe'], 2) }}
                </td>
                <td style="text-align: center; font-weight: bold;">{{ $grandTotal['ventas_cant'] }}</td>
            </tr>
        </tfoot>
    @endif
</table>
