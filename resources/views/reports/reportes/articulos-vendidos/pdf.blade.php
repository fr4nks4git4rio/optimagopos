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
                        <th rowspan="2"
                            style="text-align: center; vertical-align: middle; white-space: nowrap !important">
                            Artículo
                        </th>
                        @foreach ($sucursales as $sucursal)
                            <th colspan="2" style="white-space: nowrap !important">
                                {{ $sucursal }}
                            </th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach ($sucursales as $sucursal)
                            <th style="white-space: nowrap !important">Monto</th>
                            <th style="white-space: nowrap !important">Cant.</th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>{{ $record->producto }}</td>
                        @foreach ($sucursales as $i => $sucursal)
                            @php $celda = $record->montos[$i] ?? ['monto' => 0, 'vendidos' => 0]; @endphp
                            <td style="text-align: right">{{ number_format($celda['monto'], 2) }}</td>
                            <td style="text-align: center">{{ $celda['vendidos'] }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 1 + count($sucursales) * 2 }}"
                            style="text-align: center; padding: 10px; background-color: #c5f7e9;">
                            No se encontraron resultados...
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if (count($records) > 0)
                <tfoot>
                    <tr style="background-color: #1d1a1a; color: #fff; font-weight: bold;">
                        <td style="text-align: right">Total General</td>
                        @foreach ($sucursales as $i => $sucursal)
                            @php $totalGeneral = $grandTotal[$i] ?? ['monto' => 0, 'vendidos' => 0]; @endphp
                            <td style="text-align: right">{{ number_format($totalGeneral['monto'], 2) }}</td>
                            <td style="text-align: center">{{ $totalGeneral['vendidos'] }}</td>
                        @endforeach
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</body>

</html>
