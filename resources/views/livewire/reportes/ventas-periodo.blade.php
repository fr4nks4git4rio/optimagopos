@section('title', 'Ventas por Período')

<div wire:init="init">
    <div wire:loading.delay.longer>
        <div class="loading">
            <img src="{{ asset('img/loading.gif') }}" />
        </div>
    </div>

    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row justify-content-end">
        {{-- <div class="col-lg-auto mb-3"> --}}
        {{-- <div class="input-group"> --}}
        {{-- <span class="input-group-text"><x-icon name="search"/></span> --}}
        {{-- <input type="search" placeholder="Buscar Facturas" --}}
        {{-- class="form-control" wire:model.debounce.500ms="search"> --}}
        {{-- </div> --}}
        {{-- </div> --}}
        <div class="col-lg-auto mb-3">
            <x-dropdown icon="eye" :label="__($perPage)">
                @foreach ($perPages as $perPage)
                    <x-dropdown-item label="{{ $perPage }}" click="$set('perPage', '{{ $perPage }}')" />
                @endforeach
            </x-dropdown>
        </div>
    </div>
    <div class="row mb-1">
        <div class="col-sm-2">
            <x-input label="Fecha Inicio" type="date" :lazy="true" model="fechaInicio" />
        </div>
        <div class="col-sm-2">
            <x-input label="Fecha Fin" type="date" :lazy="true" model="fechaFin" />
        </div>
        <div class="col-sm-4">
            <x-select2 label="Sucursal" placeholder="Seleccione..." class="form-control" :options="$sucursales"
                model="sucursal" :dynamic="true" />
        </div>
    </div>

    <div class="list-group mb-3 table-responsive">
        <table class="table table-responsive table-striped">
            <thead>
                <tr>
                    @foreach ($sorts as $sort)
                        <th class="text-center cursor-pointer" style="white-space: nowrap !important"
                            wire:click="changeSort('{{ $sort }}')">
                            <span>
                                @if ($this->sort == $sort)
                                    <i class="{{ $this->class_sort }}"></i>
                                @endif {{ $sort }}
                            </span>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td class="text-center">{{ $record->fecha_transaccion_str }}</td>
                        <td class="text-center">{{ $record->sucursal }}</td>
                        <td class="text-center">${{ number_format($record->monto_mxn, 2) }}</td>
                        <td class="text-center">${{ number_format($record->monto_usd, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="list-group-item">
                                No se encontraron resultados...
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination :links="$records" :count="true" />

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
