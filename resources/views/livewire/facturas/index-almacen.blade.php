@section('title', 'Almacén de Facturas')

<div>
    <div wire:loading.delay.longer>
        <div class="loading">
            <img src="{{asset('img/loading.gif')}}" />
        </div>
    </div>

    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row justify-content-end" wire:init="init()">
        {{-- <div class="col-lg-auto mb-3">--}}
        {{-- <div class="input-group">--}}
        {{-- <span class="input-group-text"><x-icon name="search"/></span>--}}
        {{-- <input type="search" placeholder="Buscar Facturas"--}}
        {{-- class="form-control" wire:model.debounce.500ms="search">--}}
        {{-- </div>--}}
        {{-- </div>--}}
        <div class="col-lg-auto mb-3">
            <button type="button" class="btn btn-site-primary mr-1"
                wire:click="imprimirFacturas()">
                Imprimir
            </button>
            <button type="button" class="btn btn-site-primary mr-1"
                wire:click="exportarExcelFacturas()">
                Exportar EXCEL
            </button>
            <x-dropdown icon="eye" :label="__($perPage)">
                @foreach($perPages as $perPage)
                <x-dropdown-item label="{{$perPage}}" click="$set('perPage', '{{ $perPage }}')" />
                @endforeach
            </x-dropdown>

            <x-dropdown icon="sort-down-alt" :label="__($sort)">
                @foreach($sorts as $sort)
                <x-dropdown-item :label="__($sort)" click="$set('sort', '{{ $sort }}')" />
                @endforeach
            </x-dropdown>

            {{-- <x-dropdown icon="filter" :label="__($filter)">--}}
            {{-- @foreach($filters as $filter)--}}
            {{-- <x-dropdown-item :label="__($filter)" click="$set('filter', '{{ $filter }}')"/>--}}
            {{-- @endforeach--}}
            {{-- </x-dropdown>--}}
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
            <x-select2-ajax label="Cliente" placeholder="Seleccione..."
                class="form-control" url="{{route('clientes.load-clientes')}}"
                model="cliente" />
        </div>
        <div class="col-sm-2">
            <div class="mb-1">
                <label for="">Estado</label>
                <select class="form-control" wire:model="estado">
                    @foreach($estados as $estado)
                    <option value="{{$estado}}">{{$estado}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-2">
            <x-input label="Folio Interno" type="text" :lazy="true" model="folioInterno" />
        </div>
    </div>
    <div class="row">
        <div class="col-sm-2">
            <div class="mb-1">
                <label for="">Moneda</label>
                <select class="form-control" wire:model="moneda">
                    @foreach($monedas as $moneda)
                    <option value="{{$moneda}}">{{$moneda}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-2">
            <x-input label="Importe" type="number" :lazy="true" model="importe" />
        </div>
    </div>

    <div class="list-group mb-3 table-responsive">
        <table class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th class="text-center">F. Int.</th>
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
                    case 'TIMBRADA':
                        $classEstado = 'bg-success';
                        break;
                    case 'CANCELADA':
                        $classEstado = 'bg-danger';
                        break;
                    default:
                        $classEstado = 'bg-info-subtle text-dark';
                        break;
                }
                $classTipo = 'bg-success';
                ?>
                <tr>
                    <td>{{$factura->fecha_certificacion}}</td>
                    <td class="text-center">
                        {{$factura->folio_interno}}
                    </td>
                    <td class="text-center">
                        <span class="badge {{$classTipo}}">{{$factura->tipo}}</span>
                    </td>
                    <td>{{$factura->receptor}}</td>
                    <td class="text-center">
                        <span class="badge {{$classEstado}}">{{$factura->estado}}</span>
                    </td>
                    <td class="text-center">{{$factura->moneda}}</td>
                    <td class="text-center">{{number_format($factura->subtotal, 2)}}</td>
                    <td class="text-center">{{number_format($factura->iva, 2)}}</td>
                    <td class="text-center">{{number_format($factura->total, 2)}}</td>
                    <td class="text-center">
                        <ul class="list-unstyled mb-0">
                            <li class="list-inline-item mb-1">
                                <x-action icon="file-pdf" title="Mostrar PDF"
                                    click="showPdf({{$factura->id}})" />
                            </li>
                            <li class="list-inline-item mb-1">
                                <x-action icon="download" title="Descargar XML"
                                    click="descargarXml({{$factura->id}})" />
                            </li>
                            @if($factura->estado == 'TIMBRADA')
                            <li class="list-inline-item mb-1">
                                <x-action icon="x-octagon" title="Cancelar"
                                    click="$emit('openModal', 'facturas.cancel', {factura: '{{$factura->id}}', scope: 'facturas.index-almacen'})" />
                            </li>
                            @endif
                            @if($factura->estado == 'CANCELADA')
                            <li class="list-inline-item mb-1">
                                <x-action icon="trash" title="Eliminar"
                                    click="$emit('openModal', 'facturas.delete', {factura: '{{$factura->id}}', scope: 'facturas.index-almacen'})" />
                            </li>
                            @endif
                        </ul>
                    </td>
                </tr>
                @if($factura->estado == 'CANCELADA')
                <tr>
                    <td colspan="9" class="bg-white p-1">
                        <p><strong>Motivo de Cancelación: </strong> {{ $factura->motivo_cancelacion }}</p>
                    </td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="9">
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

    @if($iframeContainerClass)
    <div class="modal {{$iframeContainerClass}}">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        wire:click="$set('iframeContainerClass', '')"></button>
                </div>
                <div class="modal-body pb-0 text-center">
                    <div class="row">
                        <iframe src="{{$iframeSrc}}" frameborder="0" id="frame-death-file" height="500px"></iframe>
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
