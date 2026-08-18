@section('title', __('site.invoices.index.title'))

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
        {{-- class="form-control" wire:model.live.debounce.500ms="search"> --}}
        {{-- </div> --}}
        {{-- </div> --}}
        <div class="col-lg-auto mb-3">
            @if (user()->can('create', [App\Models\Factura::class]))
                <button type="button" class="btn btn-site-primary mr-1" wire:click="nuevaFactura">
                    {{ __('site.invoices.index.new_invoice') }}
                </button>
            @endif
            <button type="button" class="btn btn-site-primary mr-1" wire:click="imprimirFacturas()">
                {{ __('site.common.print') }}
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
            <x-input label="{{ __('site.invoices.index.start_date') }}" type="date" :lazy="true"
                model="fechaInicio" />
        </div>
        <div class="col-sm-2">
            <x-input label="{{ __('site.invoices.index.end_date') }}" type="date" :lazy="true"
                model="fechaFin" />
        </div>
        <div class="col-sm-4">
            <x-select2-ajax label="{{ __('site.invoices.index.client') }}"
                placeholder="{{ __('site.common.select') }}..." class="form-control"
                url="{{ route('clientes.load-comensales') }}" model="cliente" />
        </div>
        <div class="col-sm-2">
            <div class="mb-1">
                <label for="">{{ __('site.invoices.index.status') }}</label>
                <select class="form-control" wire:model.live="estado">
                    <option value="">{{ __('site.common.all') }}</option>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado }}">{{ __('site.statuses.invoices.' . $estado) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-2">
            <div class="mb-1">
                <label for="">{{ __('site.invoices.index.currency') }}</label>
                <select class="form-control" wire:model.live="moneda">
                    <option value="">{{ __('site.common.all') }}</option>
                    @foreach ($monedas as $moneda)
                        <option value="{{ $moneda }}">{{ $moneda }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-2">
            <x-input label="{{ __('site.invoices.index.import') }}" type="number" :lazy="true" model="importe" />
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
                    <th class="text-center">{{ __('site.common.actions') }}</th>
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
                            <span
                                class="badge {{ $classEstado }}">{{ __('site.statuses.invoices.' . $factura->estado) }}</span>
                        </td>
                        <td class="text-center">{{ $factura->moneda }}</td>
                        <td class="text-center">{{ number_format($factura->subtotal, 2) }}</td>
                        <td class="text-center">{{ number_format($factura->iva, 2) }}</td>
                        <td class="text-center">{{ number_format($factura->total, 2) }}</td>
                        <td class="text-center">
                            <ul class="list-unstyled mb-0">
                                @if ($factura->estado == 'PRECAPTURADA' || $factura->estado == 'CAPTURADA')
                                    @can('update', App\Models\Factura::find($factura->id))
                                        <li class="list-inline-item mb-1">
                                            <x-action icon="pencil" title="{{ __('site.common.edit') }}"
                                                href="{{ route('cliente.pre-facturas.save', $factura->id) }}" />
                                        </li>
                                    @endcan
                                @endif
                                <li class="list-inline-item mb-1">
                                    <x-action icon="file-pdf" title="{{ __('site.common.pdf') }}"
                                        click="showPdf({{ $factura->id }})" />
                                </li>
                                @if ($factura->estado == 'CAPTURADA')
                                    <li class="list-inline-item mb-1">
                                        <x-action icon="bell" title="{{ __('site.common.stamp') }}"
                                            click="timbrar('{{ $factura->id }}')" />
                                    </li>
                                @endif
                                @can('delete', App\Models\Factura::find($factura->id))
                                    <li class="list-inline-item mb-1">
                                        <x-action icon="trash" title="{{ __('site.common.delete') }}"
                                            click="$dispatch('openModal', { component: 'facturas.delete', arguments: {factura: '{{ $factura->id }}', scope: 'facturas.index-pre-facturas'} })" />
                                    </li>
                                @endcan
                            </ul>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="list-group-item">
                                {{ __('site.common.results_not_found') }}...
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination :links="$facturas" :count="true" />

    <div class="modal fade" id="pdf-prefacturas">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pb-0 text-center">
                    <div class="row">
                        <iframe src="{{ $iframeSrc }}" frameborder="0" id="frame-death-file"
                            style="height: 80dvh"></iframe>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('Cerrar') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
