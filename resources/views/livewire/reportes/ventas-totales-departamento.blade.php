@section('title', __('site.reports.total_sales_by_department.title'))

<div wire:init="init">
    <div wire:loading.delay.longer>
        <div class="loading">
            <div class="spinner-border text-primary my-3" role="status"><span class="visually-hidden">{{ __('site.common.loading') }}...</span></div>
        </div>
    </div>

    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row justify-content-between">
        <div class="row mb-1 col-md-10">
            <div class="col-sm-2">
                <x-input label="{{ __('site.reports.total_sales_by_department.start_date') }}" type="date" :lazy="true"
                    model="fechaInicio" />
            </div>
            <div class="col-sm-2">
                <x-input label="{{ __('site.reports.total_sales_by_department.end_date') }}" type="date" :lazy="true"
                    model="fechaFin" />
            </div>
            <div class="col-sm-8">
                <x-select2-multiple label="{{ __('site.reports.total_sales_by_department.branches') }}"
                    placeholder="{{ __('site.common.select') }}..." class="form-control" :options="$sucursalesAll"
                    model="sucursal" :lazy="true" :dynamic="true" />
            </div>
        </div>
        <div class="mb-1 col-md-2 text-end">
            @can('reportsTotalSalesByDepartment-print')
                <button type="button" class="btn btn-site-primary mr-1" wire:click="imprimirPdf()">
                    <span class="bi bi-file-pdf"></span>
                    {{ __('site.common.print') }}
                </button>
            @endcan
            @can('reportsTotalSalesByDepartment-export')
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
                            {{ __('site.reports.total_sales_by_department.sales') }}</th>
                    </tr>
                    <tr>
                        <th class="text-end" style="white-space: nowrap !important">
                            {{ __('site.reports.total_sales_by_department.amount') }}</th>
                        <th class="text-center" style="white-space: nowrap !important">
                            {{ __('site.reports.total_sales_by_department.quantity') }}</th>
                    </tr>
                </thead>
            @endif
            <tbody>
                @forelse($records as $sucursal_id => $sucursalData)
                    @foreach ($sucursalData['departamentos'] as $record)
                        <tr>
                            @if ($loop->first)
                                <td class="text-center align-middle"
                                    rowspan="{{ count($sucursalData['departamentos']) }}">
                                    {{ $sucursalData['sucursal'] }}
                                </td>
                            @endif
                            <td class="text-center">{{ $record->nombre }}</td>
                            <td class="text-end">{{ number_format($record->ventas_importe, 2) }}</td>
                            <td class="text-center">{{ $record->ventas_cant }}</td>
                        </tr>
                    @endforeach

                    {{-- Totalizador por sucursal --}}
                    <tr class="table-success fw-bold">
                        <td class="text-end" colspan="2">{{ __('site.reports.total_sales_by_department.total') }}
                            {{ $sucursalData['sucursal'] }}</td>
                        <td class="text-end">{{ number_format($sucursalData['totales']['ventas_importe'], 2) }}</td>
                        <td class="text-center">{{ $sucursalData['totales']['ventas_cant'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">
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
                        <td colspan="2" class="text-end">{{ __('site.reports.total_sales_by_department.grand_total') }}</td>
                        <td class="text-end">{{ number_format($grandTotal['ventas_importe'], 2) }}</td>
                        <td class="text-center">{{ $grandTotal['ventas_cant'] }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <div class="modal fade" id="pdf-ventas-totales-departamento">
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
                            style="width: 100%; height: 80dvh;"></iframe>
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
