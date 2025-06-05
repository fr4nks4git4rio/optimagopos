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
            font-size: 10px;
            padding: 0 !important;
            font-family: Helvetica, sans-serif;
        }

        small {
            font-size: 8px !important;
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

        .table thead tr th,
        .table tbody tr td {
            padding: 5px 5px;
            border-bottom: 1px solid lightgrey
        }

        .table-styled thead tr th {
            background: #ce5124;
            color: #fff;
            border: 1px solid #fff;
            text-align: center;
            font-size: 15px;
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

        p.break {
            -ms-word-break: break-all;
            word-break: break-all;
            word-break: break-word;
            /* Sólo WebKit -NO DOCUMENTADO */

            -ms-hyphens: auto;
            /* Guiones para separar en sílabas */
            -moz-hyphens: auto;
            /*  depende de lang en <html>      */
            -webkit-hyphens: auto;
            hyphens: auto;
        }
    </style>
</head>

<body>
    <div class="contenido">
        <table class="table">
            <tbody>
                <tr>
                    <td style="width: 65%; vertical-align: top">
                        <h1>{{$owner->razon_social}}</h1>
                        <p><strong>RFC: </strong>{{$owner->rfc}}</p>
                        <p>{{$owner->direccion_plain}}</p>
                        <p><strong>Lugar de
                                Expedición: </strong>{{$owner->codigo_postal}} {{$owner->direccion_fiscal ? optional($owner->direccion_fiscal->estado)->nombre : ''}}
                        </p>
                        <p><strong>Régimen Fiscal: </strong>{{optional($owner->regimen_fiscal)->codigo}}
                            - {{optional($owner->regimen_fiscal)->descripcion}}</p>
                    </td>
                    <td style="width: 15%; vertical-align: top">
                        <small><strong>Folio:</strong></small><br>
                        <small><strong>Fecha emisión:</strong></small><br>
                        <small><strong>Folio fiscal:</strong></small><br>
                        <small><strong>Moneda:</strong></small><br>
                        <small><strong>Forma de Pago:</strong></small><br>
                        <small><strong>Método de Pago:</strong></small><br>
                        <small><strong>Lugar de expedición:</strong></small><br>
                        <small><strong>Régimen fiscal:</strong></small><br>
                        <small><strong>Tipo de Comprobante:</strong></small><br>
                        <small><strong>Uso CFDI:</strong></small><br>
                        <small><strong>Exportación:</strong></small><br>
                    </td>
                    <td style="vertical-align: top">
                        <small>{{$factura->folio_interno}}</small><br>
                        <small>{{$factura->fecha_emision->format('d M Y, H:i')}}</small><br>
                        <small>{{$factura->uuid}}</small><br>
                        <small>{{$factura->moneda ." (TC ".($factura->moneda == 'MXN' ? '1.000000' : $factura->tipo_cambio).")"}}</small><br>
                        <small>{{$factura->forma_pago->nombre}}</small><br>
                        <small>{{$factura->metodo_pago->nombre}}</small><br>
                        <small>{{$factura->lugar_expedicion}}</small><br>
                        <small>{{optional($owner->regimen_fiscal)->nombre}}</small><br>
                        <small>{{$factura->tipo_comprobante->nombre}}</small><br>
                        <small>{{$factura->cfdi->nombre}}</small><br>
                        <small>No Aplica (01)</small><br>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <p><strong>Emitido a</strong></p>
        <p>{{$cliente->razon_social}}</p>
        <p>{{"RFC: " . $cliente->rfc}}</p>
        <p>{{"Dirección Fiscal: MEX, " . $cliente->codigo_postal}}</p>
        <p>{{"Régimen Fiscal: " . optional($cliente->regimen_fiscal)->nombre}}</p>
        <br>
        @if($factura->cfdis_relacionados)
        <p><strong>Concepto de Relación:</strong>&nbsp;{{$factura->tipo_relacion_factura->label}}</p>
        <p><strong>Facturas Relacionadas</strong></p>
        <ul>
            @foreach (explode(",", $factura->cfdis_relacionados) as $f)
            <li> {{ $f }} </li>
            @endforeach
        </ul>
        <br>
        @endif
        <table class="table">
            <thead>
                <tr>
                    <th style="background: rgb(201, 209, 217)">CTD</th>
                    <th style="background: rgb(201, 209, 217)">CONCEPTO</th>
                    <th style="background: rgb(201, 209, 217)">U DE M</th>
                    <th style="background: rgb(201, 209, 217)">P UNITARIO</th>
                    <th style="background: rgb(201, 209, 217)">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($factura->factura_conceptos as $concepto)
                <tr>
                    <td>{{$concepto->cantidad}}</td>
                    <td>{{$concepto->descripcion}}</td>
                    <td>{{$concepto->clave_unidad->label}}</td>
                    <td>{{'$' . number_format($concepto->precio_unitario, 2)}}</td>
                    <td>{{'$' . number_format($concepto->precio_unitario, 2)}}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="3" style="border-bottom: none">
                        <p style="text-transform: uppercase;">{{$factura->cantidad_letras}}</p>
                    </td>
                    <td style="text-align: right; border-bottom: none"><strong>SUBTOTAL</strong></td>
                    <td style="text-align: right; border-bottom: none"><strong>{{'$' . number_format($factura->subtotal, 2)}}</strong></td>
                </tr>
                <tr>
                    <td colspan="4" style="text-align: right; border-bottom: none"><strong>{{'IVA (' . $factura->porciento_iva . '%)'}}</strong></td>
                    <td style="text-align: right; border-bottom: none"><strong>{{'$' . number_format($factura->iva, 2)}}</strong></td>
                </tr>
                @if($factura->porciento_ret_iva > 0)
                <tr>
                    <td colspan="4" style="text-align: right; border-bottom: none"><strong>{{'Retenc. IVA (' . $factura->porciento_ret_iva . '%)'}}</strong></td>
                    <td style="text-align: right; border-bottom: none"><strong>{{'$' . number_format($factura->ret_iva, 2)}}</strong></td>
                </tr>
                @endif
                <tr>
                    <td colspan="4" style="text-align: right; border-bottom: none"><strong>TOTAL</strong></td>
                    <td style="text-align: right; border-bottom: none"><strong>{{'$' . number_format($factura->total, 2)}}</strong></td>
                </tr>
            </tbody>
        </table>
        <br>
        <p><strong>Comentarios</strong></p>
        <p>{!! $factura->comentarios !!}</p>
        <br>
        <table class="table">
            <tbody>
                <tr>
                    <td style="border-bottom: 0">
                        <p><small><strong>Fecha y Hora de Certificación</strong></small></p>
                        <p style="margin-bottom: 5px"><small>{{$factura->fecha_certificacion_str}}</small></p>
                        <p><small><strong>No de Serie del Certificado de Sello Digital del Emisor:</strong></small></p>
                        <p class="break" style="margin-bottom: 5px">
                            <small>{{$factura->numero_serie_emisor}}</small>
                        </p>
                        <p><small><strong>No de Serie del Certificado de Sello Digital del Emisor:</strong></small></p>
                        <p class="break" style="margin-bottom: 5px"><small>{{$factura->numero_serie_sat}}</small>
                        </p>
                        <p><small><strong>Sello Digital del Emisor</strong></small></p>
                        <p class="break" style="margin-bottom: 5px"><small>{{$factura->sello_digital_cfdi}}</small></p>
                        <p><small><strong>Sello Digital del SAT</strong></small></p>
                        <p class="break" style="margin-bottom: 5px"><small>{{$factura->sello_digital_sat}}</small></p>
                        <p><small><strong>Cadena Original del complemento de certificación digital del SAT:</strong></small></p>
                        <p class="break" style="margin-bottom: 5px"><small>{{$factura->cadena_original}}</small></p>
                    </td>
                    <td style="border-bottom: 0; vertical-align: top">
                        @if($factura->direccion_codigo_qr && file_exists(\Illuminate\Support\Facades\Storage::path("/public/$factura->direccion_codigo_qr")))
                        <img src="{{public_path($factura->direccion_codigo_qr_relativa)}}" alt="" style="height: 140px">
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
