<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $name }}</title>
    <style>
        @page {
            margin: 90px 20px 60px 20px;
        }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 10px;
            color: #222;
        }

        /* ---------- Encabezado ---------- */
        .header {
            position: fixed;
            top: -70px;
            left: 0;
            right: 0;
            height: 75px;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
        }

        .header table {
            width: 100%;
            border-collapse: collapse;
        }

        .header td {
            vertical-align: middle;
        }

        .header .logo-cell {
            width: 90px;
        }

        .header .logo-cell img {
            max-height: 55px;
            max-width: 85px;
        }

        .company-name {
            font-size: 15px;
            font-weight: bold;
            color: #1a1a1a;
        }

        .company-meta {
            font-size: 8.5px;
            color: #666;
        }

        .report-title {
            font-size: 13px;
            font-weight: bold;
            text-align: right;
            color: #333;
        }

        .report-subtitle {
            font-size: 9px;
            text-align: right;
            color: #666;
        }

        .filters-box {
            margin-bottom: 20px;
            font-size: 9px;
            color: #444;
        }

        .filters-box strong {
            color: #222;
        }

        /* ---------- Tabla principal ---------- */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #ccc;
            padding: 5px 6px;
        }

        table.data-table thead th {
            background-color: #065F46;
            color: #ffffff;
            text-align: center;
            font-size: 9px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        table.data-table tbody td {
            text-align: center;
            font-size: 9px;
        }

        table.data-table tbody td.text-left {
            text-align: left;
        }


        table.data-table tbody tr.row-even td {
            background-color: #dee0e2;
        }

        .text-right {
            text-align: right;
        }

        .uppercase {
            text-transform: uppercase;
        }

        /* Badge de estado */
        .badge-estado {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-vigente {
            background-color: #d4f4e2;
            color: #0d6b3f;
        }

        .badge-cancelada {
            background-color: #fbdcdc;
            color: #a12727;
        }

        /* Fila de motivo de cancelación */
        tr.cancel-row td {
            background-color: #fff8f8;
            text-align: left;
            font-size: 8.5px;
            color: #a12727;
            padding: 4px 8px;
        }

        /* Total general */
        tr.grand-total-row td {
            background-color: #065F46;
            color: #ffffff;
            font-weight: bold;
            font-size: 10px;
        }

        .no-results {
            text-align: center;
            padding: 20px;
            font-size: 11px;
            color: #888;
        }

        /* ---------- Pie de página ---------- */
        .footer {
            position: fixed;
            bottom: -50px;
            left: 0;
            right: 0;
            height: 40px;
            border-top: 1px solid #ddd;
            padding-top: 4px;
        }

        .footer table {
            width: 100%;
        }

        .footer .footer-logo {
            width: 40px;
        }

        .footer .footer-logo img {
            max-height: 60px;
            max-width: 70px;
            opacity: 0.7;
        }

        .footer .footer-text {
            font-size: 8px;
            color: #888;
        }

        .footer .footer-page {
            font-size: 8px;
            color: #888;
            text-align: right;
        }
    </style>
</head>

<body>

    @php
        $empresa = auth()
            ->user()
            ->hasAnyRole(['Admin', 'Manager'])
            ? App\Models\Cliente::decryptInfo(auth()->user()->cliente)
            : App\Models\Cliente::decryptInfo(get_system_owner());

        $ownerLogoPath = public_path('images/logo_' . (user()->lang ?: config('app.locale')) . '.png');

        if (
            auth()
                ->user()
                ->hasAnyRole(['Admin', 'Manager'])
        ) {
            $logoPath = $empresa->logo
                ? Illuminate\Support\Facades\Storage::disk('logos')->path($empresa->logo)
                : public_path('img/no_image.png');
        } else {
            $logoPath = $ownerLogoPath;
        }
    @endphp

    {{-- ================= ENCABEZADO (fijo en todas las páginas) ================= --}}
    <div class="header">
        <table>
            <tr>
                @if ($logoPath && file_exists($logoPath))
                    <td class="logo-cell">
                        <img src="{{ $logoPath }}" alt="Logo">
                    </td>
                @endif
                <td>
                    <div class="company-name">{{ $empresa->nombre_comercial }}</div>
                    @if (!empty($empresa->rfc) || !empty($empresa->direccion_fiscal))
                        <div class="company-meta">
                            RFC: {{ $empresa->rfc ?? '' }}
                            @if (!empty($empresa->direccion_fiscal))
                                &nbsp;|&nbsp; Dirección: {{ $empresa->direccion_plain }}
                            @endif
                        </div>
                    @endif
                </td>
                <td>
                    <div class="report-title">{{ __('site.invoices.index.title') }}</div>
                    <div class="report-subtitle">
                        {{ __('site.common.generated_on', ['date' => \Carbon\Carbon::now()->format('d/m/Y H:i')]) }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ================= FILTROS APLICADOS (si aplica) ================= --}}
    <div class="filters-box">
        @if (!empty($fechaInicio) || !empty($fechaFin))
            <strong>{{ __('site.common.period') }}:</strong> {{ $fechaInicio ?: '-' }} al {{ $fechaFin ?: '-' }}
        @endif
        @if (!empty($cliente))
            &nbsp;|&nbsp;<strong>{{ __('site.invoices.index.client') }}:</strong> {{ $cliente }}
        @endif
        @if (!empty($estado))
            &nbsp;|&nbsp;<strong>{{ __('site.invoices.index.status') }}:</strong> {{ $estado }}
        @endif
        @if (!empty($moneda))
            &nbsp;|&nbsp;<strong>{{ __('site.invoices.index.currency') }}:</strong> {{ $moneda }}
        @endif
        @if (!empty($importe))
            &nbsp;|&nbsp;<strong>{{ __('site.invoices.index.import') }}:</strong> {{ $importe }}
        @endif
    </div>

    {{-- ================= TABLA DE DATOS ================= --}}
    <table class="data-table">
        <thead>
            <tr>
                <th>{{ __('site.invoices.index.date') }}</th>
                <th>{{ __('site.invoices.index.receiver') }}</th>
                <th>{{ __('site.invoices.index.status') }}</th>
                <th>{{ __('site.invoices.index.currency') }}</th>
                <th>{{ __('site.invoices.index.subtotal') }}</th>
                <th>{{ __('site.invoices.index.iva') }}</th>
                <th>{{ __('site.invoices.index.total') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($facturas as $index => $factura)
                @php
                    $tipo = $factura->es_complemento ? 'COMP' : 'FACT';
                    $esCancelada = $factura->estado == 'CANCELADA';
                @endphp
                <tr class="{{ $index % 2 == 1 ? 'row-even' : '' }}">
                    <td>{{ $factura->fecha_emision_str }}</td>
                    <td class="text-left">{{ $factura->receptor }}</td>
                    <td>
                        <span class="badge-estado {{ $esCancelada ? 'badge-cancelada' : 'badge-vigente' }}">
                            {{ $factura->estado }}
                        </span>
                    </td>
                    <td>{{ $factura->moneda }}</td>
                    <td class="text-right">{{ number_format($factura->subtotal, 2) }}</td>
                    <td class="text-right">{{ number_format($factura->iva, 2) }}</td>
                    <td class="text-right">{{ number_format($factura->total, 2) }}</td>
                </tr>
                @if ($esCancelada)
                    <tr class="cancel-row">
                        <td colspan="7">
                            <strong>{{ __('site.invoices.index.cancellation_motive') }}:</strong>
                            {{ $factura->motivo_cancelacion }}
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="7" class="no-results">
                        {{ __('site.common.results_not_found') }}
                    </td>
                </tr>
            @endforelse
        </tbody>

        @if (count($facturas) > 0)
            <tfoot>
                <tr class="grand-total-row">
                    <td colspan="4" class="text-right uppercase">{{ __('site.common.general_total') }}</td>
                    <td class="text-right">{{ number_format($facturas->sum('subtotal'), 2) }}</td>
                    <td class="text-right">{{ number_format($facturas->sum('iva'), 2) }}</td>
                    <td class="text-right">{{ number_format($facturas->sum('total'), 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- ================= PIE DE PÁGINA (fijo, con logo y numeración) ================= --}}
    <div class="footer">
        <table>
            <tr>
                @if ($logoPath && file_exists($logoPath))
                    <td class="footer-logo">
                        <img src="{{ $logoPath }}" alt="Logo">
                    </td>
                @endif
                <td class="footer-text">
                    {{ config('app.name') }} &copy; {{ date('Y') }}
                </td>
                <td class="footer-page">
                    <script type="text/php">
                        if (isset($pdf)) {
                            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
                            $font = $fontMetrics->get_font("Helvetica", "normal");
                            $size = 8;
                            $pdf->page_text(480, 782, $text, $font, $size, array(0.53, 0.53, 0.53));
                        }
                    </script>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
