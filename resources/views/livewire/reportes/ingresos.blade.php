@section('title', __('site.reports.income.title'))

<div>
    <h1 class="h2 fw-bold">@yield('title')</h1>

    <div class="row mb-3">
        <div class="col-2">
            <label for="">{{__('site.reports.income.start_date')}}:</label>
            <input type="date" class="form-control" wire:model.live="fechaInicio">
        </div>
        <div class="col-2">
            <label for="">{{__('site.reports.income.end_date')}}:</label>
            <input type="date" class="form-control" wire:model.live="fechaFin">
        </div>
        <div class="col-4">
            <x-select2-ajax class="form-control" label="{{ __('site.reports.income.client') }}:" placeholder="{{__('site.common.select')}}..."
                url="{{ route('clientes.load-clientes', ['is_filter' => 1]) }}" model="cliente" />
        </div>
        <div class="col-2">
            <label for="">{{__('site.reports.income.currency')}}:</label>
            <select class="form-control " wire:model.live="moneda">
                <option value="">{{__('site.common.all')}}</option>
                @foreach ($monedas as $moneda)
                    <option value="{{ $moneda }}">{{ $moneda }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-2">
            <label for="">{{__('site.reports.income.import')}}</label>
            <input type="text" class="form-control" wire:model.live="importe">
        </div>
    </div>
    <div class="row justify-content-end">
        <div class="col-lg-auto mb-3">
            <button type="button" class="btn btn-primary" wire:click="imprimirListadoIngresos">{{__('site.common.print')}}</button>
            <button type="button" class="btn btn-primary" wire:click="exportarExcelListadoIngresos">{{__('site.common.export')}}</button>
            <x-dropdown icon="eye" :label="__($perPage)">
                @foreach ($perPages as $perPage)
                    @if ($this->perPage === $perPage)
                        <x-dropdown-item class="active opacity-50" label="{{ $perPage }}"
                            click="$set('perPage', '{{ $perPage }}')" />
                    @else
                        <x-dropdown-item label="{{ $perPage }}" click="$set('perPage', '{{ $perPage }}')" />
                    @endif
                @endforeach
            </x-dropdown>
        </div>
    </div>

    <div class="list-group mb-3 table-responsive">
        <table class="table table-responsive table-striped">
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
                    <th class="text-center" style="width: 150px">{{__('site.common.actions')}}</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_mxn = 0;
                $total_usd = 0;
                ?>
                @forelse($ingresos as $ingreso)
                    <?php
                    if ($ingreso->moneda === 'MXN') {
                        $total_mxn += $ingreso->monto;
                    } elseif ($ingreso->moneda === 'USD') {
                        $total_usd += $ingreso->monto;
                    }
                    ?>
                    <tr>
                        <td>{{ $ingreso->fecha_str }}</td>
                        <td>{{ $ingreso->folio_interno }}</td>
                        <td>{{ Illuminate\Support\Facades\Crypt::decrypt($ingreso->razon_social) }}</td>
                        <td>{{ $ingreso->uuid }}</td>
                        <td>{{ $ingreso->moneda }}</td>
                        <td>${{ number_format($ingreso->monto, 2) }}</td>
                        <td class="text-center">
                            <ul class="list-unstyled mb-0">
                                <li class="list-inline-item">
                                    <x-action icon="file-pdf" title="{{__('site.common.print')}}"
                                        click="imprimirFactura({{ $ingreso->factura_id }})" />
                                </li>
                                <li class="list-inline-item">
                                    <x-action icon="eye" title="{{__('site.common.details')}}"
                                        click="$dispatch('openModal', { component: 'cuentas-cobrar.show', arguments: {factura: '{{ $ingreso->factura_id }}'} })" />
                                </li>
                                <li class="list-inline-item">
                                    <x-action icon="file-earmark-post" title="{{__('site.common.print')}}"
                                        click="imprimirIngresoPdf({{ $ingreso->id }})" />
                                </li>
                            </ul>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="list-group-item">
                                {{__('site.common.results_not_found')}}...
                            </div>
                        </td>
                    </tr>
                @endforelse
                @if (count($ingresos) > 0)
                    <tr>
                        <td colspan="5" class="text-end fw-bold">
                            {{__('site.reports.income.total')}} MXN:
                        </td>
                        <td class="fw-bold">${{ number_format($total_mxn, 2) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-end fw-bold">
                            {{__('site.reports.income.total')}} USD:
                        </td>
                        <td class="fw-bold">${{ number_format($total_usd, 2) }}</td>
                        <td></td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <x-pagination :links="$ingresos" :count="true" />

    <div class="modal fade" id="pdf-ingresos" tabindex="-1" wire:ignore.self>
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
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('Cerrar') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
