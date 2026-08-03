<table>
    <thead>
        <tr>
            <td colspan="{{ 1 + count($sucursales) * 2 }}"
                style="font-weight: bold; font-size: 16px; text-align: center;">{{ $name }}</td>
        </tr>
        <tr></tr>
        <tr>
            @foreach ($sorts as $sort)
                <th rowspan="2" style="text-align: center; vertical-align: middle; white-space: nowrap !important">
                    {{ $sort }}
                </th>
            @endforeach
            @foreach ($sucursales as $sucursal)
                <th colspan="2" style="text-align: center">
                    {{ $sucursal }}
                </th>
            @endforeach
        </tr>
        <tr>
            @foreach ($sucursales as $sucursal)
                <th style="text-align: center;">Monto</th>
                <th style="text-align: center;">Cant.</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse($records as $record)
            <tr>
                <td style="text-align: center; vertical-align: middle;">
                    {{ $record->producto }}
                </td>
                @foreach ($sucursales as $i => $sucursal)
                    @php $celda = $record->montos[$i] ?? ['monto' => 0, 'vendidos' => 0]; @endphp
                    <td style="text-align: end;">{{ number_format($celda['monto'], 2) }}</td>
                    <td style="text-align: center;">{{ $celda['vendidos'] }}</td>
                @endforeach
            </tr>
        @empty
            <tr>
                <td colspan="{{ 1 + count($sucursales) * 2 }}" style="text-align: center;">
                    No se encontraron resultados...
                </td>
            </tr>
        @endforelse
    </tbody>
    @if (count($records) > 0)
        <tfoot>
            <tr class="table-dark fw-bold">
                <td style="text-align: end;">Total General</td>
                @foreach ($sucursales as $i => $sucursal)
                    @php $totalGeneral = $grandTotal[$i] ?? ['monto' => 0, 'vendidos' => 0]; @endphp
                    <td style="text-align: end;">{{ number_format($totalGeneral['monto'], 2) }}</td>
                    <td style="text-align: center;">{{ $totalGeneral['vendidos'] }}</td>
                @endforeach
            </tr>
        </tfoot>
    @endif
</table>
