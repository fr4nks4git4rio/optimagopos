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
                    {{ __('site.reports.daily_sales.branches') }}: &nbsp;
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
            @foreach ($sucursalData['fechas'] as $record)
                <tr>
                    @if ($loop->first)
                        <td style="text-align: center; vertical-align: middle;"
                            rowspan="{{ count($sucursalData['fechas']) + 1 }}">
                            {{ $sucursalData['sucursal'] }}
                        </td>
                    @endif
                    <td style="text-align: center">{{ $record->fecha_transaccion_str }}</td>
                    <td style="text-align: right">{{ number_format($record->monto, 2) }}</td>
                    <td style="text-align: center">{{ $record->ventas }}</td>
                </tr>
            @endforeach

            {{-- Totalizador por sucursal --}}
            <tr>
                <td colspan="2" style="text-align: right; font-weight: bold;">
                    {{ __('site.reports.daily_sales.total') }} {{ $sucursalData['sucursal'] }}</td>
                @php $totalCelda = $sucursalData['totales'] ?? ['monto' => 0, 'ventas' => 0]; @endphp
                <td style="text-align: right; font-weight: bold;">
                    {{ number_format($totalCelda['monto'], 2) }}</td>
                <td style="text-align: center; font-weight: bold;">
                    {{ $totalCelda['ventas'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">
                    <div>
                        {{ __('site.common.results_not_found') }}...
                    </div>
                </td>
            </tr>
        @endforelse
    </tbody>
    @if (count($records) > 0)
        <tfoot>
            <tr>
                <td colspan="2" style="text-align: right; font-weight: bold;">
                    {{ __('site.reports.daily_sales.grand_total') }}</td>
                @php $totalGeneral = $grandTotal ?? ['monto' => 0, 'ventas' => 0]; @endphp
                <td style="text-align: right; font-weight: bold;">{{ number_format($totalGeneral['monto'], 2) }}
                </td>
                <td style="text-align: center; font-weight: bold;">{{ $totalGeneral['ventas'] }}</td>
            </tr>
        </tfoot>
    @endif
</table>
