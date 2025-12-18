@section('title', 'Facturas')

<div>
    <div wire:loading.delay.longer>
        <div class="loading">
            <img src="{{ asset('img/loading.gif') }}" />
        </div>
    </div>

    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row justify-content-end" wire:init="init()">
        {{-- <div class="col-lg-auto mb-3"> --}}
        {{-- <div class="input-group"> --}}
        {{-- <span class="input-group-text"><x-icon name="search"/></span> --}}
        {{-- <input type="search" placeholder="Buscar Facturas" --}}
        {{-- class="form-control" wire:model.debounce.500ms="search"> --}}
        {{-- </div> --}}
        {{-- </div> --}}
        <div class="col-lg-auto mb-3">
            <button type="button" class="btn btn-site-primary mr-1" wire:click="nuevaFactura">
                Nueva Factura
            </button>
            <button type="button" class="btn btn-site-primary mr-1" wire:click="imprimirFacturas()">
                Imprimir
            </button>
            <x-dropdown icon="eye" :label="__($perPage)">
                @foreach ($perPages as $perPage)
                    <x-dropdown-item label="{{ $perPage }}" click="$set('perPage', '{{ $perPage }}')" />
                @endforeach
            </x-dropdown>

            <x-dropdown icon="sort-down-alt" :label="__($sort)">
                @foreach ($sorts as $sort)
                    <x-dropdown-item :label="__($sort)" click="$set('sort', '{{ $sort }}')" />
                @endforeach
            </x-dropdown>

            {{-- <x-dropdown icon="filter" :label="__($filter)"> --}}
            {{-- @foreach ($filters as $filter) --}}
            {{-- <x-dropdown-item :label="__($filter)" click="$set('filter', '{{ $filter }}')"/> --}}
            {{-- @endforeach --}}
            {{-- </x-dropdown> --}}
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
            <x-select2-ajax label="Cliente" placeholder="Seleccione..." class="form-control"
                url="{{ route('clientes.load-comensales') }}" model="cliente" />
        </div>
        <div class="col-sm-2">
            <div class="mb-1">
                <label for="">Estado</label>
                <select class="form-control" wire:model="estado">
                    @foreach ($estados as $estado)
                        <option value="{{ $estado }}">{{ $estado }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-2">
            <div class="mb-1">
                <label for="">Moneda</label>
                <select class="form-control" wire:model="moneda">
                    @foreach ($monedas as $moneda)
                        <option value="{{ $moneda }}">{{ $moneda }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-2">
            <x-input label="Importe" type="number" :lazy="true" model="importe" />
        </div>
    </div>

    <div class="list-group mb-3 table-responsive">
        <table class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Receptor</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Moneda</th>
                    <th class="text-center">Subtotal</th>
                    <th class="text-center">IVA</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($facturas as $factura)
                    <?php
                    switch ($factura->estado) {
                        case 'PRECAPTURADA':
                            $classEstado = 'bg-primary';
                            break;
                        case 'CAPTURADA':
                            $classEstado = 'bg-success';
                            break;
                        default:
                            $classEstado = 'bg-info-subtle text-dark';
                            break;
                    }
                    $classTipo = 'bg-success';
                    ?>
                    <tr>
                        <td>{{ $factura->fecha_emision_str }}</td>
                        <td>{{ $factura->receptor }}</td>
                        <td class="text-center">
                            <span class="badge {{ $classEstado }}">{{ $factura->estado }}</span>
                        </td>
                        <td class="text-center">{{ $factura->moneda }}</td>
                        <td class="text-center">{{ number_format($factura->subtotal, 2) }}</td>
                        <td class="text-center">{{ number_format($factura->iva, 2) }}</td>
                        <td class="text-center">{{ number_format($factura->total, 2) }}</td>
                        <td class="text-center">
                            <ul class="list-unstyled mb-0">
                                @if ($factura->estado == 'PRECAPTURADA' || $factura->estado == 'CAPTURADA')
                                    <li class="list-inline-item mb-1">
                                        <x-action icon="pencil" title="Editar"
                                            href="{{ route('pre-facturas.save', $factura->id) }}" />
                                    </li>
                                @endif
                                <li class="list-inline-item mb-1">
                                    <x-action icon="file-pdf" title="Mostrar PDF"
                                        click="showPdf({{ $factura->id }})" />
                                </li>
                                @if ($factura->estado == 'CAPTURADA')
                                    <li class="list-inline-item mb-1">
                                        <x-action icon="bell" title="Timbrar"
                                            click="timbrar('{{ $factura->id }}')" />
                                    </li>
                                @endif
                                <li class="list-inline-item mb-1">
                                    <x-action icon="trash" title="Eliminar"
                                        click="$emit('openModal', 'facturas.delete', {factura: '{{ $factura->id }}', scope: 'facturas.index-almacen'})" />
                                </li>
                            </ul>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="list-group-item">
                                No se encontraron resultados...
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination :links="$facturas" :count="true" />

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
