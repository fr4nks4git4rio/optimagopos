@section('title', __('site.invoices.index_storage.title'))

<div wire:init="init">
    <div wire:loading.delay.longer>
        <div class="loading">
            <img src="{{ asset('img/loading.gif') }}" />
        </div>
    </div>

    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row justify-content-end">
        <div class="col-lg-auto mb-3">
            <button type="button" class="btn btn-site-primary mr-1" wire:click="imprimirFacturas()">
                {{__('site.invoices.index_storage.print')}}
            </button>
            <button type="button" class="btn btn-site-primary mr-1" wire:click="exportarExcelFacturas()">
                {{__('site.invoices.index_storage.excel_export')}}
            </button>
            <x-dropdown icon="eye" :label="__($perPage)">
                @foreach ($perPages as $perPage)
                    <x-dropdown-item label="{{ $perPage }}" click="$set('perPage', '{{ $perPage }}')" />
                @endforeach
            </x-dropdown>
        </div>
    </div>
    <div class="row mb-1">
        <div class="col-sm-2">
            <x-input label="{{__('site.invoices.index_storage.start_date')}}" type="date" :lazy="true" model="fechaInicio" />
        </div>
        <div class="col-sm-2">
            <x-input label="{{__('site.invoices.index_storage.enda_date')}}" type="date" :lazy="true" model="fechaFin" />
        </div>
        <div class="col-sm-4">
            <x-select2-ajax label="{{__('site.invoices.index_storage.client')}}" placeholder="{{__('site.common.select')}}..." class="form-control"
                url="{{ route('clientes.load-clientes') }}" model="cliente" />
        </div>
        <div class="col-sm-2">
            <div class="mb-1">
                <label for="">{{__('site.invoices.index_storage.status')}}:</label>
                <select class="form-control" wire:model="estado">
                    @foreach ($estados as $estado)
                        <option value="{{ $estado }}">{{ $estado }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-2">
            <x-input label="{{__('site.invoices.index_storage.internal_folio')}}" type="text" :lazy="true" model="folioInterno" />
        </div>
    </div>
    <div class="row">
        <div class="col-sm-2">
            <div class="mb-1">
                <label for="">{{__('site.invoices.index_storage.currency')}}:</label>
                <select class="form-control" wire:model="moneda">
                    @foreach ($monedas as $moneda)
                        <option value="{{ $moneda }}">{{ $moneda }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-2">
            <x-input label="{{__('site.invoices.index_storage.import')}}" type="number" :lazy="true" model="importe" />
        </div>
    </div>

    <div class="list-group mb-3 table-responsive">
        <table class="table table-responsive table-striped">
            <thead>
                <tr>
                    @foreach ($sorts as $sort)
                        <th class="text-center cursor-pointer text-uppercase" style="white-space: nowrap !important"
                            wire:click="changeSort('{{ $sort }}')">
                            <span>
                                @if ($this->sort == $sort)
                                    <i class="{{ $this->class_sort }}"></i>
                                @endif {{ $sort }}
                            </span>
                        </th>
                    @endforeach
                    <th class="text-center">{{__('site.common.actions')}}</th>
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
                        <td>{{ $factura->fecha_certificacion }}</td>
                        <td class="text-center">
                            {{ $factura->folio_interno }}
                        </td>
                        <td class="text-center">
                            @php
                                $classTipo = 'bg-success';
                                switch ($factura->tipo) {
                                    case 'COMP.':
                                        $classTipo = 'bg-primary';
                                        break;
                                    case 'NOT.CRE.':
                                        $classTipo = 'bg-warning text-dark';
                                        break;
                                    default:
                                        $classTipo = 'bg-success';
                                        break;
                                }
                            @endphp
                            <span class="badge {{ $classTipo }}">{{ $factura->tipo }}</span>
                            @if ($factura->paquete)
                                <br>
                                <span class="badge bg-primary-subtle border-primary text-primary">
                                    {{ $factura->paquete }}
                                </span>
                            @endif
                        </td>
                        <td>{{ $factura->receptor }}</td>
                        <td class="text-center">
                            <span class="badge {{ $classEstado }}">{{ __('site.statuses.invoices.'.$factura->estado) }}</span>
                        </td>
                        <td class="text-center">{{ $factura->moneda }}</td>
                        <td class="text-center">{{ number_format($factura->subtotal, 2) }}</td>
                        <td class="text-center">{{ number_format($factura->iva, 2) }}</td>
                        <td class="text-center">{{ number_format($factura->total, 2) }}</td>
                        <td class="text-center">
                            <ul class="list-unstyled mb-0">
                                <li class="list-inline-item mb-1">
                                    <x-action icon="file-pdf" title="{{__('site.common.download_pdf')}}"
                                        click="showPdf({{ $factura->id }})" />
                                </li>
                                <li class="list-inline-item mb-1">
                                    <x-action icon="download" title="{{__('site.common.download_xml')}}"
                                        click="descargarXml({{ $factura->id }})" />
                                </li>
                                @if ($factura->estado == 'TIMBRADA')
                                    @can('cancelFacturaSistema', App\Models\Factura::find($factura->id))
                                        <li class="list-inline-item mb-1">
                                            <x-action icon="x-octagon" title="{{__('site.common.cancel')}}"
                                                click="$emit('openModal', 'facturas-sistema.cancel', {factura: '{{ $factura->id }}', scope: 'facturas-sistema.index-almacen'})" />
                                        </li>
                                    @endcan
                                @endif
                                @if ($factura->estado == 'CANCELADA')
                                    @can('deleteFacturaSistema', App\Models\Factura::find($factura->id))
                                        <li class="list-inline-item mb-1">
                                            <x-action icon="trash" title="{{__('site.common.delete')}}"
                                                click="$emit('openModal', 'facturas-sistema.delete', {factura: '{{ $factura->id }}', scope: 'facturas-sistema.index-almacen'})" />
                                        </li>
                                    @endcan
                                @endif
                            </ul>
                        </td>
                    </tr>
                    @if ($factura->estado == 'CANCELADA')
                        <tr>
                            <td colspan="10" class="bg-white p-1">
                                <p><strong>{{__('site.invoices.index_storage.cancellation_motive')}}: </strong> {{ $factura->motivo_cancelacion }}</p>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="10">
                            <div class="list-group-item">
                                {{__('site.common.results_not_found')}}...
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
                            wire:click="$set('iframeContainerClass', '')">{{__('site.common.close')}}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
