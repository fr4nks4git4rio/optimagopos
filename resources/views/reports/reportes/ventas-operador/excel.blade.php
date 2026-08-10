<table>
    <thead>
        <tr>
            <td colspan="6" style="font-weight: bold; font-size: 16px; text-align: center;">{{ $name }}</td>
        </tr>
        <tr></tr>
        <tr>
            <td colspan="6">
                Período:&nbsp;{{ $fechaInicio ?: '-' }} al {{ $fechaFin ?: '-' }}
                @if (!empty($sucursalesSeleccionadas))
                    &nbsp;|&nbsp;
                    Sucursal(es): &nbsp;
                    {{ Illuminate\Support\Str::replaceLast(', ', ' y ', implode(', ', $sucursalesSeleccionadas)) }}
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
            <th colspan="2" style="text-align:center; white-space: nowrap !important">Ventas</th>
            <th colspan="2" style="text-align:center; white-space: nowrap !important">Correcciones</th>
        </tr>
        <tr>
            <th style="white-space: nowrap !important; text-align: right;">Monto</th>
            <th style="white-space: nowrap !important; text-align: center;">Op.</th>
            <th style="white-space: nowrap !important; text-align: right;">Monto</th>
            <th style="white-space: nowrap !important; text-align: center;">Op.</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $sucursal_id => $sucursalData)
            @foreach ($sucursalData['operadores'] as $record)
                <tr>
                    @if ($loop->first)
                        <td style="text-align: center; vertical-align: middle;"
                            rowspan="{{ count($sucursalData['operadores']) + 1 }}">
                            {{ $sucursalData['sucursal'] }}
                        </td>
                    @endif
                    <td style="text-align: center;">{{ $record->nombre }}</td>
                    <td style="text-align: right;">{{ number_format($record->ventas_importe, 2) }}</td>
                    <td style="text-align: center;">{{ $record->ventas_cant }}</td>
                    <td style="text-align: right;">{{ number_format($record->correcciones_importe, 2) }}</td>
                    <td style="text-align: center;">{{ $record->correcciones_cant }}</td>
                </tr>
            @endforeach

            {{-- Totalizador por sucursal --}}
            <tr>
                <td style="font-weight: bold !important; text-align: right;">
                    Total {{ $sucursalData['sucursal'] }}</td>
                <td style="font-weight: bold !important; text-align: right;">
                    {{ number_format($sucursalData['totales']['ventas_importe'], 2) }}</td>
                <td style="font-weight: bold !important; text-align: center;">
                    {{ $sucursalData['totales']['ventas_cant'] }}</td>
                <td style="font-weight: bold !important; text-align: right;">
                    {{ number_format($sucursalData['totales']['correcciones_importe'], 2) }}</td>
                <td style="font-weight: bold !important; text-align: center;">
                    {{ $sucursalData['totales']['correcciones_cant'] }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">
                    No se encontraron resultados...
                </td>
            </tr>
        @endforelse
    </tbody>
    @if (count($records) > 0)
        <tfoot>
            <tr>
                <td colspan="2" style="text-align: right; font-weight: bold;">Total General</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($grandTotal['ventas_importe'], 2) }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $grandTotal['ventas_cant'] }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($grandTotal['correcciones_importe'], 2) }}
                </td>
                <td style="text-align: center; font-weight: bold;">{{ $grandTotal['correcciones_cant'] }}</td>
            </tr>
        </tfoot>
    @endif
</table>
