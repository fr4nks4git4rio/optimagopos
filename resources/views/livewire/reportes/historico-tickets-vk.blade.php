@section('title', __('site.reports.vk_ticket_history.title'))

<div>
    <div wire:loading.delay.longer>
        <div class="loading">
            <div class="spinner-border text-primary my-3" role="status"><span class="visually-hidden">Cargando...</span></div>
        </div>
    </div>

    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row justify-content-between">
        <div class="row mb-1 col-md-10">
            <div class="col-sm-2">
                <x-input label="{{ __('site.reports.vk_ticket_history.start_date') }}" type="date" :lazy="true"
                    model="fechaInicio" />
            </div>
            <div class="col-sm-2">
                <x-input label="{{ __('site.reports.vk_ticket_history.end_date') }}" type="date" :lazy="true"
                    model="fechaFin" />
            </div>
            <div class="col-sm-2">
                <x-select2-multiple label="{{ __('site.reports.vk_ticket_history.statuses') }}"
                    placeholder="{{ __('site.common.select') }}..." class="form-control" :options="$estadosAll"
                    model="estado" :lazy="true" :dynamic="true" />
            </div>
            <div class="col-sm-3">
                <x-select2-multiple label="{{ __('site.reports.vk_ticket_history.branches') }}"
                    placeholder="{{ __('site.common.select') }}..." class="form-control" :options="$sucursalesAll"
                    model="sucursal" :lazy="true" :dynamic="true" />
            </div>
            <div class="col-sm-3">
                <x-select2-multiple label="{{ __('site.reports.vk_ticket_history.terminals') }}"
                    placeholder="{{ __('site.common.select') }}..." class="form-control" :options="$terminalesAll"
                    model="terminal" :lazy="true" :dynamic="true" />
            </div>
        </div>
        <div class="mb-1 col-md-2 text-end">
            @can('reportsVKTicketHistory-print')
                <button type="button" class="btn btn-site-primary mr-1" wire:click="imprimirPdf()">
                    <span class="bi bi-file-pdf"></span>
                    {{ __('site.common.print') }}
                </button>
            @endcan
            @can('reportsVKTicketHistory-export')
                <button type="button" class="btn btn-site-primary mr-1" wire:click="exportarExcel()">
                    <span class="bi bi-file-excel"></span>
                    {{ __('site.common.export') }}
                </button>
            @endcan
        </div>
    </div>


    <div class="list-group mb-3 table-responsive">
        <table class="table table-responsive table-striped table-bordered">
            @if (count($records) > 0)
                <thead>
                    <tr>
                        @foreach ($sorts as $sort)
                            <th class="text-center align-middle cursor-pointer" rowspan="2"
                                style="white-space: nowrap !important" wire:click="changeSort('{{ $sort }}')">
                                <span>
                                    @if ($this->sort == $sort)
                                        <i class="{{ $this->class_sort }}"></i>
                                    @endif {{ $sort }}
                                </span>
                            </th>
                        @endforeach
                        <th class="text-center" colspan="2" style="white-space: nowrap !important">
                            {{ __('site.statuses.tickets_vk.Open') }}
                        </th>
                        <th class="text-center" colspan="2" style="white-space: nowrap !important">
                            {{ __('site.statuses.tickets_vk.InProcess') }}
                        </th>
                        <th class="text-center" colspan="2" style="white-space: nowrap !important">
                            {{ __('site.statuses.tickets_vk.Delayed') }}
                        </th>
                        <th class="text-center align-middle" rowspan="2" style="white-space: nowrap !important">
                            {{ __('site.statuses.tickets_vk.Done') }}
                        </th>
                    </tr>
                    <tr>
                        <th class="text-center" style="white-space: nowrap !important">
                            {{ __('site.reports.vk_ticket_history.date') }}</th>
                        <th class="text-center" style="white-space: nowrap !important">
                            {{ __('site.reports.vk_ticket_history.duration') }}</th>
                        <th class="text-center" style="white-space: nowrap !important">
                            {{ __('site.reports.vk_ticket_history.date') }}</th>
                        <th class="text-center" style="white-space: nowrap !important">
                            {{ __('site.reports.vk_ticket_history.duration') }}</th>
                        <th class="text-center" style="white-space: nowrap !important">
                            {{ __('site.reports.vk_ticket_history.date') }}</th>
                        <th class="text-center" style="white-space: nowrap !important">
                            {{ __('site.reports.vk_ticket_history.duration') }}</th>
                    </tr>
                </thead>
            @endif
            <tbody>
                @forelse($records as $sucursal_id => $sucursalData)
                    @foreach ($sucursalData['records'] as $record)
                        <tr>
                            @if ($loop->first)
                                <td class="text-center align-middle" rowspan="{{ count($sucursalData['records']) }}">
                                    {{ $sucursalData['sucursal'] }}
                                </td>
                            @endif
                            <td class="text-center">{{ $record->id_transaccion }}</td>
                            <td class="text-center">{{ $record->terminal }}</td>
                            <td class="text-center">{{ $record->fecha_transaccion_str ?? '-' }}</td>
                            <td class="text-center">
                                {{ $record->tiempo_abierto ?? '-' }}
                                @if (!$record->fecha_terminado && $record->fecha_transaccion)
                                    <i class="text-muted"
                                        title="{{ __('site.reports.vk_ticket_history.ongoing_detail') }}">
                                        ({{ __('site.reports.vk_ticket_history.ongoing') }})
                                    </i>
                                @endif
                            </td>
                            <td class="text-center">{{ $record->fecha_en_proceso_str ?? '-' }}</td>
                            <td class="text-center">
                                {{ $record->tiempo_en_proceso ?? '-' }}
                                @if (!$record->fecha_terminado && $record->fecha_en_proceso)
                                    <i class="text-muted"
                                        title="{{ __('site.reports.vk_ticket_history.ongoing_detail') }}">
                                        ({{ __('site.reports.vk_ticket_history.ongoing') }})</i>
                                @endif
                            </td>
                            <td class="text-center">{{ $record->fecha_demorado_str ?? '-' }}</td>
                            <td class="text-center">
                                {{ $record->tiempo_demorado ?? '-' }}
                                @if (!$record->fecha_terminado && $record->fecha_demorado)
                                    <i class="text-muted"
                                        title="{{ __('site.reports.vk_ticket_history.ongoing_detail') }}">
                                        ({{ __('site.reports.vk_ticket_history.ongoing') }})
                                    </i>
                                @endif
                            </td>
                            <td class="text-center">{{ $record->fecha_terminado_str ?? '-' }}</td>
                        </tr>
                    @endforeach
                    <tr class="table-success fw-bold">
                        <td colspan="3" class="text-end">{{ __('site.reports.vk_ticket_history.totals') }}
                            {{ $sucursalData['sucursal'] }}</td>
                        <td class="text-center" colspan="2">
                            {{ $sucursalData['totales']['tickets_abiertos'] }}
                            ({{ __('site.reports.vk_ticket_history.average_time') }}:
                            {{ $sucursalData['totales']['promedio_tickets_abiertos'] }})
                        </td>
                        <td colspan="2"></td>
                        <td class="text-center" colspan="2">
                            {{ $sucursalData['totales']['tickets_demorados'] }}
                            ({{ __('site.reports.vk_ticket_history.average_time') }}:
                            {{ $sucursalData['totales']['promedio_tickets_demorados'] }})
                        </td>
                        <td></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($this->sorts) + 7 }}" class="text-center">
                            <div class="list-group-item">
                                {{ __('site.common.results_not_found') }}...
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if (count($records) > 0)
                <tfoot>
                    <tr class="table-dark fw-bold">
                        <td colspan="3" class="text-end">{{ __('site.reports.vk_ticket_history.grand_total') }}</td>
                        <td class="text-center" colspan="2">
                            {{ $totalGeneral['tickets_abiertos'] }}
                            ({{ __('site.reports.vk_ticket_history.average_time') }}:
                            {{ $totalGeneral['promedio_tickets_abiertos'] }})
                        </td>
                        <td colspan="2"></td>
                        <td class="text-center" colspan="2">
                            {{ $totalGeneral['tickets_demorados'] }}
                            ({{ __('site.reports.vk_ticket_history.average_time') }}:
                            {{ $totalGeneral['promedio_tickets_demorados'] }})</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <div class="modal fade" id="pdf-historico-vk">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        wire:click="$set('iframeContainerClass', '')"></button>
                </div>
                <div class="modal-body pb-0 text-center">
                    <div class="row">
                        <iframe src="{{ $iframeSrc }}" frameborder="0" id="frame-death-file"
                            style="height: 80dvh"></iframe>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        wire:click="$set('iframeContainerClass', '')">{{ __('Cerrar') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
