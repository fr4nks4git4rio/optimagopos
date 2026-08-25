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
            margin-bottom: 10px;
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
            margin-bottom: 10px;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #ccc;
            padding: 4px 6px;
        }

        table.data-table thead th {
            background-color: #065f46;
            color: #ffffff;
            text-align: center;
            font-size: 9px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        table.data-table thead tr.sub-header th {
            background-color: #065f46;
            font-size: 8.5px;
        }

        table.data-table tbody td {
            text-align: center;
            font-size: 9px;
        }

        table.data-table tbody td.text-left {
            text-align: left;
        }

        table.data-table tbody td.text-right {
            text-align: right;
        }

        table.data-table tbody tr.row-even td {
            background-color: #dee0e2;
        }

        tr.subtotal-row td {
            background-color: #dfeee2;
            font-weight: bold;
            border-top: 1.5px solid #7fb894;
        }

        tr.grand-total-row td {
            background-color: #065f46;
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
        $empresa = App\Models\Cliente::decryptInfo(auth()->user()->cliente);
        $logoPath = $empresa->logo
            ? Illuminate\Support\Facades\Storage::disk('logos')->path($empresa->logo)
            : public_path('img/no_image.png');
        $ownerLogoPath = public_path('images/logo_' . (user()->lang ?: config('app.locale')) . '.png');
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
                                &nbsp;|&nbsp; {{__('site.address.address')}}: {{ $empresa->direccion_plain }}
                            @endif
                        </div>
                    @endif
                </td>
                <td>
                    <div class="report-title">{{ $name }}</div>
                    <div class="report-subtitle">
                        {{ __('site.common.generated_on', ['date' => \Carbon\Carbon::now()->format('d/m/Y H:i')]) }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ================= FILTROS APLICADOS ================= --}}
    <div class="filters-box">
        <strong>{{__('site.common.period')}}:</strong> {{ $fechaInicio ?: '-' }} al {{ $fechaFin ?: '-' }}
        @if (!empty($sucursalesSeleccionadas))
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>{{__('site.reports.daily_sales.branches')}}:</strong>
            {{ Illuminate\Support\Str::replaceLast(', ', ' '.__('site.common.and').' ', implode(', ', $sucursalesSeleccionadas)) }}
        @endif
    </div>

    {{-- ================= TABLA DE DATOS ================= --}}
    <table class="data-table">
        @if (count($records) > 0)
            <thead>
                <tr>
                    @foreach ($sorts as $sort)
                        <th rowspan="2">{{ $sort }}</th>
                    @endforeach
                    @foreach ($formasPago as $formaPago)
                        <th colspan="2">{{ $formaPago }}</th>
                    @endforeach
                </tr>
                <tr class="sub-header">
                    @foreach ($formasPago as $formaPago)
                        <th style="text-align: right">{{__('site.reports.daily_sales.amount')}}</th>
                        <th>{{__('site.reports.daily_sales.quantity')}}</th>
                    @endforeach
                </tr>
            </thead>
        @endif

        <tbody>
            @forelse($records as $sucursal_id => $sucursalData)
                @php
                    $totalFilas = count($sucursalData['fechas']);
                    $pos = 0;
                @endphp

                @foreach ($sucursalData['fechas'] as $index => $record)
                    <tr class="{{ $pos % 2 == 1 ? 'row-even' : '' }}">
                        @if ($pos == 0)
                            <td class="text-left" rowspan="{{ $totalFilas }}">
                                {{ $sucursalData['sucursal'] }}
                            </td>
                        @endif
                        <td>{{ $record->fecha_transaccion_str }}</td>
                        @foreach ($formasPago as $i => $formaPago)
                            @php $celda = $record->montos[$i] ?? ['monto' => 0, 'operaciones' => 0]; @endphp
                            <td class="text-right">{{ number_format($celda['monto'], 2) }}</td>
                            <td>{{ $celda['operaciones'] }}</td>
                        @endforeach
                    </tr>
                    @php $pos++; @endphp
                @endforeach

                {{-- Totalizador por sucursal --}}
                <tr class="subtotal-row">
                    <td colspan="2" style="text-align: right">{{__('site.reports.daily_sales.total')}} {{ $sucursalData['sucursal'] }}</td>
                    @foreach ($formasPago as $i => $formaPago)
                        @php $totalCelda = $sucursalData['totales'][$i] ?? ['monto' => 0, 'operaciones' => 0]; @endphp
                        <td style="text-align: right">{{ number_format($totalCelda['monto'], 2) }}</td>
                        <td style="text-align: center">{{ $totalCelda['operaciones'] }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 2 + count($formasPago) * 2 }}" class="no-results">
                        {{__('site.common.results_not_found')}}...
                    </td>
                </tr>
            @endforelse
        </tbody>

        @if (count($records) > 0)
            <tfoot>
                <tr class="grand-total-row">
                    <td colspan="2" style="text-align: right">{{__('site.reports.daily_sales.grand_total')}}</td>
                    @foreach ($formasPago as $i => $formaPago)
                        @php $totalGeneral = $grandTotal[$i] ?? ['monto' => 0, 'operaciones' => 0]; @endphp
                        <td style="text-align: right">{{ number_format($totalGeneral['monto'], 2) }}</td>
                        <td style="text-align: center">{{ $totalGeneral['operaciones'] }}</td>
                    @endforeach
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- ================= PIE DE PÁGINA (fijo, con logo y numeración) ================= --}}
    <div class="footer">
        <table>
            <tr>
                @if (file_exists($ownerLogoPath))
                    <td class="footer-logo">
                        <img src="{{ $ownerLogoPath }}" alt="Logo">
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
