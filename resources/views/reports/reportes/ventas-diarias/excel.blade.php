<table>
    <thead>
        <tr>
            <td colspan="{{ 2 + count($formasPago) * 2 }}" style="font-weight: bold; font-size: 16px; text-align: center;">{{ $name }}</td>
        </tr>
        <tr></tr>
        <tr>
            @foreach ($sorts as $sort)
                <th rowspan="2" style="text-align: center; vertical-align: middle; white-space: nowrap !important">
                    {{ $sort }}
                </th>
            @endforeach
            @foreach ($formasPago as $formaPago)
                <th colspan="2" style="text-align: center;">
                    {{ $formaPago }}
                </th>
            @endforeach
        </tr>
        <tr>
            @foreach ($formasPago as $formaPago)
                <th style="text-align: center;">Monto</th>
                <th style="text-align: center;">Op.</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse($records as $sucursal_id => $sucursalData)
            @foreach ($sucursalData['fechas'] as $record)
                <tr>
                    @if ($loop->first)
                        <td style="text-align: center; vertical-align: middle;" rowspan="{{ count($sucursalData['fechas']) + 1 }}">
                            {{ $sucursalData['sucursal'] }}
                        </td>
                    @endif
                    <td style="text-align: center">{{ $record->fecha_transaccion_str }}</td>
                    @foreach ($formasPago as $i => $formaPago)
                        @php $celda = $record->montos[$i] ?? ['monto' => 0, 'operaciones' => 0]; @endphp
                        <td style="text-align: right">{{ number_format($celda['monto'], 2) }}</td>
                        <td style="text-align: center">{{ $celda['operaciones'] }}</td>
                    @endforeach
                </tr>
            @endforeach

            {{-- Totalizador por sucursal --}}
            <tr>
                <td>
                    Total {{ $sucursalData['sucursal'] }}</td>
                @foreach ($formasPago as $i => $formaPago)
                    @php $totalCelda = $sucursalData['totales'][$i] ?? ['monto' => 0, 'operaciones' => 0]; @endphp
                    <td style="text-align: right; font-weight: bold;">
                        {{ number_format($totalCelda['monto'], 2) }}</td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ $totalCelda['operaciones'] }}</td>
                @endforeach
            </tr>
        @empty
            <tr>
                <td colspan="{{ 2 + count($formasPago) * 2 }}">
                    <div>
                        No se encontraron resultados...
                    </div>
                </td>
            </tr>
        @endforelse
    </tbody>
    @if (count($records) > 0)
        <tfoot>
            <tr>
                <td colspan="2" style="text-align: right; font-weight: bold;">Total General</td>
                @foreach ($formasPago as $i => $formaPago)
                    @php $totalGeneral = $grandTotal[$i] ?? ['monto' => 0, 'operaciones' => 0]; @endphp
                    <td style="text-align: right; font-weight: bold;">{{ number_format($totalGeneral['monto'], 2) }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $totalGeneral['operaciones'] }}</td>
                @endforeach
            </tr>
        </tfoot>
    @endif
</table>
