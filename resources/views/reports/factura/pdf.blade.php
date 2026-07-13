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
                        <h1>{{ $owner->razon_social }}</h1>
                        <p><strong>{{ __('site.invoices.pdf_invoice.rfc') }}: </strong>{{ $owner->rfc }}</p>
                        <p>{{ $owner->direccion_plain }}</p>
                        <p><strong>{{ __('site.invoices.pdf_invoice.postal_code') }}: </strong>{{ $owner->codigo_postal }}
                            {{ $owner->direccion_fiscal ? optional($owner->direccion_fiscal->estado)->nombre : '' }}
                        </p>
                        <p><strong>{{ __('site.invoices.pdf_invoice.fiscal_regime') }}: </strong>{{ optional($owner->regimen_fiscal)->codigo }}
                            - {{ optional($owner->regimen_fiscal)->descripcion }}</p>
                    </td>
                    <td style="width: 15%; vertical-align: top">
                        <small><strong>{{ __('site.invoices.pdf_invoice.internal_folio') }}:</strong></small><br>
                        <small><strong>{{ __('site.invoices.pdf_invoice.issued_date') }}:</strong></small><br>
                        <small><strong>{{ __('site.invoices.pdf_invoice.fiscal_folio') }}:</strong></small><br>
                        <small><strong>{{ __('site.invoices.pdf_invoice.currency') }}:</strong></small><br>
                        <small><strong>{{ __('site.invoices.pdf_invoice.payment_form') }}:</strong></small><br>
                        <small><strong>{{ __('site.invoices.pdf_invoice.payment_method') }}:</strong></small><br>
                        <small><strong>{{ __('site.invoices.pdf_invoice.postal_code') }}:</strong></small><br>
                        <small><strong>{{ __('site.invoices.pdf_invoice.fiscal_regime') }}:</strong></small><br>
                        <small><strong>{{ __('site.invoices.pdf_invoice.receipt_type') }}:</strong></small><br>
                        <small><strong>{{ __('site.invoices.pdf_invoice.cfdi') }}:</strong></small><br>
                        <small><strong>{{ __('site.invoices.pdf_invoice.to_export') }}:</strong></small><br>
                    </td>
                    <td style="vertical-align: top">
                        <small>{{ $factura->folio_interno }}</small><br>
                        <small>{{ $factura->fecha_emision->format('d M Y, H:i') }}</small><br>
                        <small>{{ $factura->uuid }}</small><br>
                        <small>{{ $factura->moneda . ' (TC ' . number_format($factura->tipo_cambio, 6) . ')' }}</small><br>
                        <small>{{ $factura->forma_pago->nombre }}</small><br>
                        <small>{{ $factura->metodo_pago->nombre }}</small><br>
                        <small>{{ $factura->lugar_expedicion }}</small><br>
                        <small>{{ optional($owner->regimen_fiscal)->nombre }}</small><br>
                        <small>{{ $factura->tipo_comprobante->nombre }}</small><br>
                        <small>{{ $factura->cfdi?->nombre }}</small>
                        <small>{{ __('site.invoices.pdf_invoice.no_apply') }} (01)</small>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <p><strong>{{ __('site.invoices.pdf_invoice.emitted_to') }}</strong></p>
        <p>{{ $cliente->razon_social }}</p>
        <p>{{ __('site.invoices.pdf_invoice.rfc') . $cliente->rfc }}</p>
        <p>{{ __('site.invoices.pdf_invoice.fiscal_address') .': MEX, ' . $cliente->codigo_postal }}</p>
        <p>{{ __('site.invoices.pdf_invoice.fiscal_regime') .': ' . optional($cliente->regimen_fiscal)->nombre }}</p>
        <br>
        @if ($factura->cfdis_relacionados)
            <p><strong>{{ __('site.invoices.pdf_invoice.relation_concept') }}:</strong>&nbsp;{{ $factura->tipo_relacion_factura->label }}</p>
            <p><strong>{{ __('site.invoices.pdf_invoice.related_invoices') }}</strong></p>
            <ul>
                @foreach (explode(',', $factura->cfdis_relacionados) as $f)
                    <li> {{ $f }} </li>
                @endforeach
            </ul>
            <br>
        @endif
        <table class="table">
            <thead>
                <tr>
                    <th style="background: rgb(201, 209, 217); text-transform: uppercase">{{ __('site.invoices.pdf_invoice.ctd') }}</th>
                    <th style="background: rgb(201, 209, 217); text-transform: uppercase">{{ __('site.invoices.pdf_invoice.concept') }}</th>
                    <th style="background: rgb(201, 209, 217); text-transform: uppercase">{{ __('site.invoices.pdf_invoice.m_u') }}</th>
                    <th style="background: rgb(201, 209, 217); text-transform: uppercase">{{ __('site.invoices.pdf_invoice.unit_p') }}</th>
                    <th style="background: rgb(201, 209, 217); text-transform: uppercase">{{ __('site.invoices.pdf_invoice.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($factura->factura_conceptos as $concepto)
                    <tr>
                        <td>{{ $concepto->cantidad }}</td>
                        <td>{{ $concepto->descripcion }}</td>
                        <td>{{ $concepto->clave_unidad->label }}</td>
                        <td>{{ '$' . number_format($concepto->precio_unitario, 2) }}</td>
                        <td>{{ '$' . number_format($concepto->precio_unitario, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="3" style="border-bottom: none">
                        <p style="text-transform: uppercase;">{{ $factura->cantidad_letras }}</p>
                    </td>
                    <td style="text-align: right; border-bottom: none; text-transform: uppercase"><strong>{{ __('site.invoices.pdf_invoice.subtotal') }}</strong></td>
                    <td style="text-align: right; border-bottom: none">
                        <strong>{{ '$' . number_format($factura->subtotal, 2) }}</strong></td>
                </tr>
                <tr>
                    <td colspan="4" style="text-align: right; border-bottom: none; text-transform: uppercase">
                        <strong>{{ __('site.invoices.pdf_invoice.iva').' (' . $factura->porciento_iva . '%)' }}</strong></td>
                    <td style="text-align: right; border-bottom: none">
                        <strong>{{ '$' . number_format($factura->iva, 2) }}</strong></td>
                </tr>
                @if ($factura->porciento_ret_iva > 0)
                    <tr>
                        <td colspan="4" style="text-align: right; border-bottom: none; text-transform: uppercase">
                            <strong>{{ __('site.invoices.pdf_invoice.ret_iva') .' (' . $factura->porciento_ret_iva . '%)' }}</strong></td>
                        <td style="text-align: right; border-bottom: none">
                            <strong>{{ '$' . number_format($factura->ret_iva, 2) }}</strong></td>
                    </tr>
                @endif
                <tr>
                    <td colspan="4" style="text-align: right; border-bottom: none; text-transform: uppercase;"><strong>{{ __('site.invoices.pdf_invoice.total') }}</strong></td>
                    <td style="text-align: right; border-bottom: none">
                        <strong>{{ '$' . number_format($factura->total, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
        <br>
        <p><strong>{{ __('site.invoices.pdf_invoice.comments') }}</strong></p>
        <p>{!! $factura->comentarios !!}</p>
        <br>
        <table class="table">
            <tbody>
                <tr>
                    <td style="border-bottom: 0">
                        <p><small><strong>{{ __('site.invoices.pdf_invoice.certification_date') }}</strong></small></p>
                        <p style="margin-bottom: 5px"><small>{{ $factura->fecha_certificacion_str }}</small></p>
                        <p><small><strong>{{ __('site.invoices.pdf_invoice.issuer_digital_seal_serial_number') }}:</strong></small></p>
                        <p class="break" style="margin-bottom: 5px">
                            <small>{{ $factura->numero_serie_emisor }}</small>
                        </p>
                        <p><small><strong>{{ __('site.invoices.pdf_invoice.sat_digital_seal_serial_number') }}:</strong></small></p>
                        <p class="break" style="margin-bottom: 5px"><small>{{ $factura->numero_serie_sat }}</small>
                        </p>
                        <p><small><strong>{{ __('site.invoices.pdf_invoice.issuer_digital_seal') }}/strong></small></p>
                        <p class="break" style="margin-bottom: 5px"><small>{{ $factura->sello_digital_cfdi }}</small>
                        </p>
                        <p><small><strong>{{ __('site.invoices.pdf_invoice.sat_digital_seal') }}</strong></small></p>
                        <p class="break" style="margin-bottom: 5px"><small>{{ $factura->sello_digital_sat }}</small>
                        </p>
                        <p><small><strong>{{ __('site.invoices.pdf_invoice.original_string_sat') }}:</strong></small></p>
                        <p class="break" style="margin-bottom: 5px"><small>{{ $factura->cadena_original }}</small></p>
                    </td>
                    <td style="border-bottom: 0; vertical-align: top">
                        @if (
                            $factura->direccion_codigo_qr &&
                                file_exists(\Illuminate\Support\Facades\Storage::path("/public/$factura->direccion_codigo_qr")))
                            <img src="{{ public_path($factura->direccion_codigo_qr_relativa) }}" alt=""
                                style="height: 140px">
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
