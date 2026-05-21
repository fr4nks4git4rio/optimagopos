@section('title', 'Productos más vendidos')

<div wire:init="init">
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
        <div class="col-sm-4">
            <x-select2 label="Sucursal" placeholder="Seleccione..." class="form-control" :options="$sucursales"
                model="sucursal" :dynamic="true" />
        </div>
    </div>

    <div class="list-group mb-3 table-responsive">
        <table class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th class="text-center">Producto</th>
                    <th class="text-center">Cantidad Vendida</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td class="text-center">{{ $record->nombre }}</td>
                        <td class="text-center">{{ $record->total_vendido }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">
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
