@section('title', 'Ventas por Operador')

<div>
    <div wire:loading.delay.longer>
        <div class="loading">
            <img src="{{ asset('img/loading.gif') }}" />
        </div>
    </div>

    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row mb-1">
        <div class="col-sm-2">
            <x-input label="Fecha Inicio" type="date" :lazy="true" model="fechaInicio" />
        </div>
        <div class="col-sm-2">
            <x-input label="Fecha Fin" type="date" :lazy="true" model="fechaFin" />
        </div>
        <div class="col-sm-8">
            <x-select2-multiple label="Sucursal" placeholder="Seleccione..." class="form-control" :options="$sucursalesAll"
                model="sucursal" :lazy="true" :dynamic="true" />
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
                        <th class="text-center" colspan="2" style="white-space: nowrap !important">Ventas</th>
                        <th class="text-center" colspan="2" style="white-space: nowrap !important">Correcciones</th>
                    </tr>
                    <tr>
                        <th class="text-center" style="white-space: nowrap !important">Monto</th>
                        <th class="text-center" style="white-space: nowrap !important">Op.</th>
                        <th class="text-center" style="white-space: nowrap !important">Monto</th>
                        <th class="text-center" style="white-space: nowrap !important">Op.</th>
                    </tr>
                </thead>
            @endif
            <tbody>
                @forelse($records as $sucursal_id => $sucursalData)
                    @foreach ($sucursalData['operadores'] as $record)
                        <tr>
                            @if ($loop->first)
                                <td class="text-center align-middle"
                                    rowspan="{{ count($sucursalData['operadores']) + 1 }}">
                                    {{ $sucursalData['sucursal'] }}
                                </td>
                            @endif
                            <td class="text-center">{{ $record->nombre }}</td>
                            <td class="text-end">{{ number_format($record->ventas_cant, 2) }}</td>
                            <td class="text-end">{{ number_format($record->ventas_importe, 2) }}</td>
                            <td class="text-end">{{ number_format($record->correcciones_cant, 2) }}</td>
                            <td class="text-end">{{ number_format($record->correcciones_importe, 2) }}</td>
                        </tr>
                    @endforeach

                    {{-- Totalizador por sucursal --}}
                    <tr class="table-success fw-bold">
                        <td class="text-end">Total {{ $sucursalData['sucursal'] }}</td>
                        <td class="text-end">{{ number_format($sucursalData['totales']['ventas_cant'], 2) }}</td>
                        <td class="text-end">{{ number_format($sucursalData['totales']['ventas_importe'], 2) }}</td>
                        <td class="text-end">{{ number_format($sucursalData['totales']['correcciones_cant'], 2) }}</td>
                        <td class="text-end">{{ number_format($sucursalData['totales']['correcciones_importe'], 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="list-group-item">
                                No se encontraron resultados...
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if (count($records) > 0)
                <tfoot>
                    <tr class="table-dark fw-bold">
                        <td colspan="2" class="text-end">Total General</td>
                        <td class="text-end">{{ number_format($grandTotal['ventas_cant'], 2) }}</td>
                        <td class="text-end">{{ number_format($grandTotal['ventas_importe'], 2) }}</td>
                        <td class="text-end">{{ number_format($grandTotal['correcciones_cant'], 2) }}</td>
                        <td class="text-end">{{ number_format($grandTotal['correcciones_importe'], 2) }}</td>
                    </tr>
                </tfoot>
            @endif
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
                                height="500px"></iframe>
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
