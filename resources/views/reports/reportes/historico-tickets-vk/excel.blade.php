<table>
    <thead>
        <tr>
            <td colspan="10" style="font-weight: bold; font-size: 16px; text-align: center;">{{ $name }}</td>
        </tr>
        <tr></tr>
        <tr>
            <td colspan="10">
                {{ __('site.common.period') }}:&nbsp;{{ $fechaInicio ?: '-' }} al {{ $fechaFin ?: '-' }}
                @if (!empty($estadosSeleccionados))
                    &nbsp;|&nbsp;
                    {{ __('site.reports.vk_ticket_history.statuses') }}: &nbsp;
                    {{ Illuminate\Support\Str::replaceLast(', ', ' ' . __('site.common.and') . ' ', implode(', ', $estadosSeleccionados)) }}
                @endif
                @if (!empty($sucursalesSeleccionadas))
                    &nbsp;|&nbsp;
                    {{ __('site.reports.vk_ticket_history.branches') }}: &nbsp;
                    {{ Illuminate\Support\Str::replaceLast(', ', ' ' . __('site.common.and') . ' ', implode(', ', $sucursalesSeleccionadas)) }}
                @endif
                @if (!empty($terminalesSeleccionadas))
                    &nbsp;|&nbsp;
                    {{ __('site.reports.vk_ticket_history.terminals') }}: &nbsp;
                    {{ Illuminate\Support\Str::replaceLast(', ', ' ' . __('site.common.and') . ' ', implode(', ', $terminalesSeleccionadas)) }}
                @endif
            </td>
        </tr>
        <tr></tr>
        <tr>
            @foreach ($sorts as $sort)
                <th rowspan="2" style="text-align: center; vertical-align: middle; white-space: nowrap !important">
                    {{ $sort }}
                </th>
            @endforeach
            <th colspan="2" style="text-align: center;">
                {{ __('site.statuses.tickets_vk.Open') }}
            </th>
            <th colspan="2" style="text-align: center;">
                {{ __('site.statuses.tickets_vk.InProcess') }}
            </th>
            <th colspan="2" style="text-align: center;">
                {{ __('site.statuses.tickets_vk.Delayed') }}
            </th>
            <th style="text-align: center;">
                {{ __('site.statuses.tickets_vk.Done') }}
            </th>
        </tr>
        <tr>
            <th style="text-align: center;">{{ __('site.reports.vk_ticket_history.date') }}</th>
            <th style="text-align: center;">{{ __('site.reports.vk_ticket_history.duration') }}</th>
            <th style="text-align: center;">{{ __('site.reports.vk_ticket_history.date') }}</th>
            <th style="text-align: center;">{{ __('site.reports.vk_ticket_history.duration') }}</th>
            <th style="text-align: center;">{{ __('site.reports.vk_ticket_history.date') }}</th>
            <th style="text-align: center;">{{ __('site.reports.vk_ticket_history.duration') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $sucursal_id => $sucursalData)
            @foreach ($sucursalData['records'] as $record)
                <tr>
                    @if ($loop->first)
                        <td style="text-align: center; vertical-align: middle;"
                            rowspan="{{ count($sucursalData['records']) }}">
                            {{ $sucursalData['sucursal'] }}
                        </td>
                    @endif
                    <td style="text-align: center;">{{ $record->id_transaccion }}</td>
                    <td style="text-align: center;">{{ $record->terminal }}</td>
                    <td style="text-align: center;">{{ $record->fecha_transaccion_str ?? '-' }}</td>
                    <td style="text-align: center;">
                        {{ $record->tiempo_abierto ?? '-' }}
                        @if (!$record->fecha_terminado && $record->fecha_transaccion)
                            <i style="color: gray"> ({{ __('site.reports.vk_ticket_history.ongoing') }})</i>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ $record->fecha_en_proceso_str ?? '-' }}</td>
                    <td style="text-align: center;">
                        {{ $record->tiempo_en_proceso ?? '-' }}
                        @if (!$record->fecha_terminado && $record->fecha_en_proceso)
                            <i style="color: gray"> ({{ __('site.reports.vk_ticket_history.ongoing') }})</i>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ $record->fecha_demorado_str ?? '-' }}</td>
                    <td style="text-align: center;">
                        {{ $record->tiempo_demorado ?? '-' }}
                        @if (!$record->fecha_terminado && $record->fecha_demorado)
                            <i style="color: gray"> ({{ __('site.reports.vk_ticket_history.ongoing') }})</i>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ $record->fecha_terminado_str ?? '-' }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="3" style="text-align: right">{{ __('site.reports.vk_ticket_history.totals') }}
                    {{ $sucursalData['sucursal'] }}</td>
                <td style="text-align: center;" colspan="2">
                    {{ $sucursalData['totales']['tickets_abiertos'] }}
                    ({{ __('site.reports.vk_ticket_history.average_time') }}:
                    {{ $sucursalData['totales']['promedio_tickets_abiertos'] }})
                </td>
                <td colspan="2"></td>
                <td style="text-align: center;" colspan="2">
                    {{ $sucursalData['totales']['tickets_demorados'] }}
                    ({{ __('site.reports.vk_ticket_history.average_time') }}:
                    {{ $sucursalData['totales']['promedio_tickets_demorados'] }})
                </td>
                <td></td>
            </tr>
        @empty
            <tr>
                <td colspan="10">
                    <div>
                        {{ __('site.common.results_not_found') }}...
                    </div>
                </td>
            </tr>
        @endforelse
    </tbody>
    @if (count($records) > 0)
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right">{{ __('site.reports.vk_ticket_history.grand_total') }}
                </td>
                <td style="text-align: center" colspan="2">
                    {{ $totalGeneral['tickets_abiertos'] }}
                    ({{ __('site.reports.vk_ticket_history.average_time') }}:
                    {{ $totalGeneral['promedio_tickets_abiertos'] }})
                </td>
                <td colspan="2"></td>
                <td style="text-align: center" colspan="2">
                    {{ $totalGeneral['tickets_demorados'] }}
                    ({{ __('site.reports.vk_ticket_history.average_time') }}:
                    {{ $totalGeneral['promedio_tickets_demorados'] }})</td>
                <td></td>
            </tr>
        </tfoot>
    @endif
</table>
