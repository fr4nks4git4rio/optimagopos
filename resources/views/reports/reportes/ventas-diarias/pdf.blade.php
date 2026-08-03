<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $name }}</title>
    <style>
        @page {
            margin: 15px;
        }

        .contenido {
            font-size: 11px;
            padding: 0 !important;
            font-family: Arial, Helvetica, sans-serif;
        }

        p {
            margin: 0;
        }

        hr {
            margin-top: 0;
            margin-bottom: 0;
            border: 1px solid #065F46;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-styled thead tr th {
            background: #065F46;
            color: #fff;
            border: 1px solid #fff;
            text-align: center;
            font-size: 13px;
            padding: 10px 5px;
        }

        .table-styled tbody tr td {
            color: #000;
            border: 1px solid #fff;
            text-align: center;
            padding: 2px;
        }

        .table-styled tbody tr:nth-child(odd) td {
            background: #69ceb1;
        }

        .table-styled tbody tr:nth-child(even) td {
            background: #c5f7e9;
        }
    </style>
</head>

<body>
    <div class="contenido">
        <h1 style="text-align: center">{{ $name }}</h1>
        <br>
        <br>
        <table class="table table-styled">
            @if (count($records) > 0)
                <thead>
                    <tr>
                        @foreach ($sorts as $sort)
                            <th rowspan="2" style="white-space: nowrap !important">
                                {{ $sort }}
                            </th>
                        @endforeach
                        @foreach ($formasPago as $formaPago)
                            <th colspan="2" style="white-space: nowrap !important">
                                {{ $formaPago }}
                            </th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach ($formasPago as $formaPago)
                            <th style="white-space: nowrap !important">Monto</th>
                            <th style="white-space: nowrap !important">Op.</th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody>
                @forelse($records as $sucursal_id => $sucursalData)
                    @foreach ($sucursalData['fechas'] as $record)
                        <tr>
                            @if ($loop->first)
                                <td rowspan="{{ count($sucursalData['fechas']) + 1 }}">
                                    {{ $sucursalData['sucursal'] }}
                                </td>
                            @endif
                            <td>{{ $record->fecha_transaccion_str }}</td>
                            @foreach ($formasPago as $i => $formaPago)
                                @php $celda = $record->montos[$i] ?? ['monto' => 0, 'operaciones' => 0]; @endphp
                                <td style="text-align: right">{{ number_format($celda['monto'], 2) }}</td>
                                <td>{{ $celda['operaciones'] }}</td>
                            @endforeach
                        </tr>
                    @endforeach

                    {{-- Totalizador por sucursal --}}
                    <tr>
                        <td
                            style="background-color: #065F46 !important; color: #fff !important; font-weight: bold !important;">
                            Total {{ $sucursalData['sucursal'] }}</td>
                        @foreach ($formasPago as $i => $formaPago)
                            @php $totalCelda = $sucursalData['totales'][$i] ?? ['monto' => 0, 'operaciones' => 0]; @endphp
                            <td
                                style="background-color: #065F46 !important; color: #fff !important; font-weight: bold !important; text-align: right;">
                                {{ number_format($totalCelda['monto'], 2) }}</td>
                            <td
                                style="background-color: #065F46 !important; color: #fff !important; font-weight: bold !important;">
                                {{ $totalCelda['operaciones'] }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 2 + count($formasPago) * 2 }}"
                            style="background-color: #c5f7e9; padding: 10px; text-align: center;">
                            No se encontraron resultados...
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if (count($records) > 0)
                <tfoot>
                    <tr style="background-color: #1d1a1a; color: #fff; font-weight: bold;">
                        <td colspan="2" style="text-align: right">Total General</td>
                        @foreach ($formasPago as $i => $formaPago)
                            @php $totalGeneral = $grandTotal[$i] ?? ['monto' => 0, 'operaciones' => 0]; @endphp
                            <td style="text-align: right">{{ number_format($totalGeneral['monto'], 2) }}</td>
                            <td style="text-align: center">{{ $totalGeneral['operaciones'] }}</td>
                        @endforeach
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</body>

</html>
