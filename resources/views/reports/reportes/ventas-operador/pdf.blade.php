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
                        <th colspan="2" style="text-align-center; white-space: nowrap !important">Ventas</th>
                        <th colspan="2" style="text-align-center; white-space: nowrap !important">Correcciones</th>
                    </tr>
                    <tr>
                        <th style="white-space: nowrap !important">Monto</th>
                        <th style="white-space: nowrap !important">Op.</th>
                        <th style="white-space: nowrap !important">Monto</th>
                        <th style="white-space: nowrap !important">Op.</th>
                    </tr>
                </thead>
            @endif
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
                            <td style="text-align: end;">{{ number_format($record->ventas_importe, 2) }}</td>
                            <td style="text-align: center;">{{ $record->ventas_cant }}</td>
                            <td style="text-align: end;">{{ number_format($record->correcciones_importe, 2) }}</td>
                            <td style="text-align: center;">{{ $record->correcciones_cant }}</td>
                        </tr>
                    @endforeach

                    {{-- Totalizador por sucursal --}}
                    <tr>
                        <td
                            style="background-color: #065F46 !important; color: #fff !important; font-weight: bold !important; text-align: right;">
                            Total {{ $sucursalData['sucursal'] }}</td>
                        <td
                            style="background-color: #065F46 !important; color: #fff !important; font-weight: bold !important; text-align: end;">
                            {{ number_format($sucursalData['totales']['ventas_importe'], 2) }}</td>
                        <td
                            style="background-color: #065F46 !important; color: #fff !important; font-weight: bold !important; text-align: center;">
                            {{ $sucursalData['totales']['ventas_cant'] }}</td>
                        <td
                            style="background-color: #065F46 !important; color: #fff !important; font-weight: bold !important; text-align: end;">
                            {{ number_format($sucursalData['totales']['correcciones_importe'], 2) }}</td>
                        <td
                            style="background-color: #065F46 !important; color: #fff !important; font-weight: bold !important; text-align: center;">
                            {{ $sucursalData['totales']['correcciones_cant'] }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="background-color: #c5f7e9; padding: 10px; text-align: center;">
                            No se encontraron resultados...
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if (count($records) > 0)
                <tfoot>
                    <tr style="background-color: #1d1a1a; color: #fff; font-weight: bold;">
                        <td colspan="2" style="text-align: right;">Total General</td>
                        <td style="text-align: end;">{{ number_format($grandTotal['ventas_importe'], 2) }}</td>
                        <td style="text-align: center;">{{ $grandTotal['ventas_cant'] }}</td>
                        <td style="text-align: end;">{{ number_format($grandTotal['correcciones_importe'], 2) }}
                        </td>
                        <td style="text-align: center;">{{ $grandTotal['correcciones_cant'] }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</body>

</html>
