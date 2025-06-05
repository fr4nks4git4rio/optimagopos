<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{$name}}</title>
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
            border: 1px solid #ce5124;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-styled thead tr th {
            background: #ce5124;
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
            background: #f6d3c8;
        }

        .table-styled tbody tr:nth-child(even) td {
            background: #f9eae3;
        }
    </style>
</head>

<body>
    <div class="contenido">
        <h1 style="text-align: center">Almacén de Facturas</h1>
        <br>
        <br>
        <table class="table table-styled">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>F. Int.</th>
                    <th>Receptor</th>
                    <th>Estado</th>
                    <th>Moneda</th>
                    <th>Subtotal</th>
                    <th>IVA</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($facturas as $factura)
                <?php
                if ($factura->es_complemento)
                    $tipo = 'COMP';
                else
                    $tipo = 'FACT';
                ?>
                <tr>
                    <td>{{$factura->fecha_certificacion}}</td>
                    <td>
                        {{$factura->folio_interno}}
                    </td>
                    <td>{{\Illuminate\Support\Facades\Crypt::decrypt($factura->receptor)}}</td>
                    <td>{{$factura->estado}}</td>
                    <td>{{$factura->moneda}}</td>
                    <td>{{number_format($factura->subtotal, 2)}}</td>
                    <td>{{number_format($factura->iva, 2)}}</td>
                    <td>{{number_format($factura->total, 2)}}</td>
                </tr>
                @if($factura->estado == 'CANCELADA')
                <tr>
                    <td colspan="12" style="background-color: #fff; padding-top: 2px; padding-bottom: 2px; text-align: left;">
                        <p><strong>Motivo de Cancelación: </strong> {{ $factura->motivo_cancelacion }}</p>
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
