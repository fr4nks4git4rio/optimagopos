<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $name }}</title>
    <style>
        @page {
            margin: 90px 20px 60px 20px;
            size: landscape;
        }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 9px;
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
            padding: 4px 5px;
        }

        table.data-table thead th {
            background-color: #065f46;
            color: #ffffff;
            text-align: center;
            font-size: 8.5px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        table.data-table thead tr.sub-header th {
            background-color: #065f46;
            font-size: 8px;
        }

        table.data-table thead th.th-abierto {
            background-color: #b8860b;
        }

        table.data-table thead th.th-proceso {
            background-color: #2e6da4;
        }

        table.data-table thead th.th-demorado {
            background-color: #a94442;
        }

        table.data-table thead th.th-terminado {
            background-color: #3c763d;
        }

        table.data-table tbody td {
            text-align: center;
            font-size: 8px;
        }

        table.data-table tbody td.text-left {
            text-align: left;
        }

        table.data-table tbody tr.row-even td {
            background-color: #dee0e2;
        }

        .en-curso {
            display: block;
            font-size: 7px;
            color: #888;
            font-style: italic;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .align-middle {
            vertical-align: middle;
        }

        /* Total por sucursal */
        tr.subtotal-row td {
            background-color: #dfeee2;
            font-weight: bold;
            border-top: 1.5px solid #7fb894;
            font-size: 8px;
        }

        /* Total general */
        tr.grand-total-row td {
            background-color: #065f46;
            color: #ffffff;
            font-weight: bold;
            font-size: 8.5px;
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
                                &nbsp;|&nbsp; {{ __('site.address.address') }}: {{ $empresa->direccion_plain }}
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
        <strong>{{ __('site.common.period') }}:</strong> {{ $fechaInicio ?: '-' }} al {{ $fechaFin ?: '-' }}
        @if (!empty($estadosSeleccionados))
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>{{ __('site.reports.vk_ticket_history.statuses') }}:</strong>
            {{ Illuminate\Support\Str::replaceLast(', ', ' '.__('site.common.and').' ', implode(', ', $estadosSeleccionados)) }}
        @endif
        @if (!empty($sucursalesSeleccionadas))
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>{{ __('site.reports.vk_ticket_history.branches') }}:</strong>
            {{ Illuminate\Support\Str::replaceLast(', ', ' '.__('site.common.and').' ', implode(', ', $sucursalesSeleccionadas)) }}
        @endif
        @if (!empty($terminalesSeleccionadas))
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>{{ __('site.reports.vk_ticket_history.terminals') }}:</strong>
            {{ Illuminate\Support\Str::replaceLast(', ', ' '.__('site.common.and').' ', implode(', ', $terminalesSeleccionadas)) }}
        @endif
    </div>

    {{-- ================= TABLA DE DATOS ================= --}}
    <table class="data-table">
        @if (count($records) > 0)
            <thead>
                <tr>
                    @foreach ($sorts as $sort)
                        <th rowspan="2" class="text-center align-middle">{{ $sort }}</th>
                    @endforeach
                    <th colspan="2" class="th-abierto text-center">{{ __('site.statuses.tickets_vk.Open') }}</th>
                    <th colspan="2" class="th-proceso text-center">{{ __('site.statuses.tickets_vk.InProcess') }}
                    </th>
                    <th colspan="2" class="th-demorado text-center">{{ __('site.statuses.tickets_vk.Delayed') }}
                    </th>
                    <th rowspan="2" class="th-terminado text-center align-middle">
                        {{ __('site.statuses.tickets_vk.Done') }}
                    </th>
                </tr>
                <tr class="sub-header">
                    <th class="text-center">{{ __('site.reports.vk_ticket_history.date') }}</th>
                    <th class="text-center">{{ __('site.reports.vk_ticket_history.duration') }}</th>
                    <th class="text-center">{{ __('site.reports.vk_ticket_history.date') }}</th>
                    <th class="text-center">{{ __('site.reports.vk_ticket_history.duration') }}</th>
                    <th class="text-center">{{ __('site.reports.vk_ticket_history.date') }}</th>
                    <th class="text-center">{{ __('site.reports.vk_ticket_history.duration') }}</th>
                </tr>
            </thead>
        @endif

        <tbody>
            @forelse($records as $sucursal_id => $sucursalData)
                @php $totalFilas = count($sucursalData['records']); @endphp

                @foreach ($sucursalData['records'] as $index => $record)
                    <tr class="{{ $index % 2 == 1 ? 'row-even' : '' }}">
                        @if ($index == 0)
                            <td class="text-center align-middle" rowspan="{{ $totalFilas }}">
                                {{ $sucursalData['sucursal'] }}
                            </td>
                        @endif
                        <td class="text-center">{{ $record->id_transaccion }}</td>
                        <td class="text-center">{{ $record->terminal }}</td>

                        <td class="text-center">{{ $record->fecha_transaccion_str ?? '-' }}</td>
                        <td class="text-center">
                            {{ $record->tiempo_abierto ?? '-' }}
                            @if (!$record->fecha_terminado && $record->fecha_transaccion)
                                <span class="en-curso">({{ __('site.reports.vk_ticket_history.ongoing') }})</span>
                            @endif
                        </td>

                        <td class="text-center">{{ $record->fecha_en_proceso_str ?? '-' }}</td>
                        <td class="text-center">
                            {{ $record->tiempo_en_proceso ?? '-' }}
                            @if (!$record->fecha_terminado && $record->fecha_en_proceso)
                                <span class="en-curso">({{ __('site.reports.vk_ticket_history.ongoing') }})</span>
                            @endif
                        </td>

                        <td class="text-center">{{ $record->fecha_demorado_str ?? '-' }}</td>
                        <td class="text-center">
                            {{ $record->tiempo_demorado ?? '-' }}
                            @if (!$record->fecha_terminado && $record->fecha_demorado)
                                <span class="en-curso">({{ __('site.reports.vk_ticket_history.ongoing') }})</span>
                            @endif
                        </td>

                        <td class="text-center">{{ $record->fecha_terminado_str ?? '-' }}</td>
                    </tr>
                @endforeach

                {{-- Totalizador por sucursal --}}
                <tr class="subtotal-row">
                    <td colspan="3" class="text-end">{{ __('site.reports.vk_ticket_history.totals') }}
                        {{ $sucursalData['sucursal'] }}</td>
                    <td colspan="2" class="text-center">
                        {{ $sucursalData['totales']['tickets_abiertos'] }}
                        <span class="en-curso">({{ __('site.reports.vk_ticket_history.average_time') }}:
                            {{ $sucursalData['totales']['promedio_tickets_abiertos'] }})</span>
                    </td>
                    <td colspan="2"></td>
                    <td colspan="2" class="text-center">
                        {{ $sucursalData['totales']['tickets_demorados'] }}
                        <span class="en-curso">({{ __('site.reports.vk_ticket_history.average_time') }}:
                            {{ $sucursalData['totales']['promedio_tickets_demorados'] }})</span>
                    </td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($sorts) + 7 }}" class="no-results">
                        {{ __('site.common.results_not_found') }}
                    </td>
                </tr>
            @endforelse
        </tbody>

        @if (count($records) > 0)
            <tfoot>
                <tr class="grand-total-row">
                    <td colspan="3" class="text-end">{{ __('site.reports.vk_ticket_history.grand_total') }}</td>
                    <td colspan="2" class="text-center">
                        {{ $totalGeneral['tickets_abiertos'] }}
                        ({{ __('site.reports.vk_ticket_history.average_time') }}: {{ $totalGeneral['promedio_tickets_abiertos'] }})
                    </td>
                    <td colspan="2"></td>
                    <td colspan="2" class="text-center">
                        {{ $totalGeneral['tickets_demorados'] }}
                        ({{ __('site.reports.vk_ticket_history.average_time') }}: {{ $totalGeneral['promedio_tickets_demorados'] }})
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- ================= PIE DE PÁGINA (fijo, con logo y numeración) ================= --}}
    <div class="footer">
        <table>
            <tr>
                @if ($ownerLogoPath && file_exists($ownerLogoPath))
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
                            $pdf->page_text(730, 555, $text, $font, $size, array(0.53, 0.53, 0.53));
                        }
                    </script>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
