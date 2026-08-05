@section('title', 'Histórico de Tickets Video Kitchen')

<div>
    <div wire:loading.delay.longer>
        <div class="loading">
            <img src="{{ asset('img/loading.gif') }}" />
        </div>
    </div>

    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row justify-content-between">
        <div class="row mb-1 col-md-12">
            <div class="col-sm-2">
                <x-input label="Fecha Inicio" type="date" :lazy="true" model="fechaInicio" />
            </div>
            <div class="col-sm-2">
                <x-input label="Fecha Fin" type="date" :lazy="true" model="fechaFin" />
            </div>
            <div class="col-sm-2">
                <x-select2-multiple label="Estado" placeholder="Seleccione..." class="form-control" :options="$estadosAll"
                    model="estado" :lazy="true" :dynamic="true" />
            </div>
            <div class="col-sm-3">
                <x-select2-multiple label="Sucursal" placeholder="Seleccione..." class="form-control" :options="$sucursalesAll"
                    model="sucursal" :lazy="true" :dynamic="true" />
            </div>
            <div class="col-sm-3">
                <x-select2-multiple label="Terminal" placeholder="Seleccione..." class="form-control" :options="$terminalesAll"
                    model="terminal" :lazy="true" :dynamic="true" />
            </div>
        </div>
        {{-- <div class="mb-1 col-md-2 text-end">
            <button type="button" class="btn btn-site-primary mr-1" wire:click="imprimirPdf()">
                <span class="bi bi-file-pdf"></span>
                Imprimir
            </button>
            <button type="button" class="btn btn-site-primary mr-1" wire:click="exportarExcel()">
                <span class="bi bi-file-excel"></span>
                Exportar
            </button>
        </div> --}}
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
                            ABIERTO
                        </th>
                        <th class="text-center" colspan="2" style="white-space: nowrap !important">
                            EN PROCESO
                        </th>
                        <th class="text-center" colspan="2" style="white-space: nowrap !important">
                            DEMORADO
                        </th>
                        <th class="text-center align-middle" rowspan="2" style="white-space: nowrap !important">
                            TERMINADO
                        </th>
                    </tr>
                    <tr>
                        <th class="text-center" style="white-space: nowrap !important">Fecha/Hora</th>
                        <th class="text-center" style="white-space: nowrap !important">Duración</th>
                        <th class="text-center" style="white-space: nowrap !important">Fecha/Hora</th>
                        <th class="text-center" style="white-space: nowrap !important">Duración</th>
                        <th class="text-center" style="white-space: nowrap !important">Fecha/Hora</th>
                        <th class="text-center" style="white-space: nowrap !important">Duración</th>
                    </tr>
                </thead>
            @endif
            <tbody>
                @forelse($records as $sucursal_id => $sucursalData)
                    @foreach ($sucursalData['records'] as $record)
                        <tr>
                            @if ($loop->first)
                                <td class="text-center align-middle"
                                    rowspan="{{ count($sucursalData['records']) + 1 }}">
                                    {{ $sucursalData['sucursal'] }}
                                </td>
                            @endif
                            <td class="text-center">{{ $record->id_transaccion }}</td>
                            <td class="text-center">{{ $record->terminal }}</td>
                            <td class="text-center">{{ $record->fecha_transaccion_str ?? '-' }}</td>
                            <td class="text-center">
                                {{ $record->tiempo_abierto ?? '-' }}
                                @if (!$record->fecha_terminado && $record->fecha_transaccion)
                                    <i class="text-muted" title="Ticket aún en curso, tiempo calculado hasta ahora"> (en
                                        curso)</i>
                                @endif
                            </td>
                            <td class="text-center">{{ $record->fecha_en_proceso_str ?? '-' }}</td>
                            <td class="text-center">
                                {{ $record->tiempo_en_proceso ?? '-' }}
                                @if (!$record->fecha_terminado && $record->fecha_en_proceso)
                                    <i class="text-muted" title="Ticket aún en curso, tiempo calculado hasta ahora"> (en
                                        curso)</i>
                                @endif
                            </td>
                            <td class="text-center">{{ $record->fecha_demorado_str ?? '-' }}</td>
                            <td class="text-center">
                                {{ $record->tiempo_demorado ?? '-' }}
                                @if (!$record->fecha_terminado && $record->fecha_demorado)
                                    <i class="text-muted" title="Ticket aún en curso, tiempo calculado hasta ahora"> (en
                                        curso)</i>
                                @endif
                            </td>
                            <td class="text-center">{{ $record->fecha_terminado_str ?? '-' }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="{{ count($this->sorts) + 7 }}" class="text-center">
                            <div class="list-group-item">
                                No se encontraron resultados...
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($iframeContainerClass)
        <div class="modal {{ $iframeContainerClass }}">
            <div class="modal-dialog modal-lg">
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
    @endif
</div>
