@section('title', __('site.reports.cash_movements.title'))

<div wire:init="init">
    <div wire:loading.delay.longer>
        <div class="loading">
            <div class="spinner-border text-primary my-3" role="status"><span
                    class="visually-hidden">{{ __('site.common.loading') }}...</span></div>
        </div>
    </div>

    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row justify-content-between">
        <div class="row mb-1 col-md-10">
            <div class="col-sm-2">
                <x-input label="{{ __('site.reports.cash_movements.start_date') }}" type="date" :lazy="true"
                    model="fechaInicio" />
            </div>
            <div class="col-sm-2">
                <x-input label="{{ __('site.reports.cash_movements.end_date') }}" type="date" :lazy="true"
                    model="fechaFin" />
            </div>
            <div class="col-sm-8">
                <x-select2-multiple label="{{ __('site.reports.cash_movements.branches') }}"
                    placeholder="{{ __('site.common.select') }}..." class="form-control" :options="$sucursalesAll"
                    model="sucursal" :lazy="true" :dynamic="true" />
            </div>
        </div>
        <div class="mb-1 col-md-2 text-end">
            @can('reportsCashMovements-print')
                <button type="button" class="btn btn-site-primary mr-1" wire:click="imprimirPdf()">
                    <span class="bi bi-file-pdf"></span>
                    {{ __('site.common.print') }}
                </button>
            @endcan
            @can('reportsCashMovements-export')
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
                            <th class="text-center align-middle cursor-pointer" style="white-space: nowrap !important"
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
            @endif
            <tbody>
                @forelse($records as $sucursal_id => $sucursalData)
                    @foreach ($sucursalData['movimientos'] as $record)
                        <tr>
                            @if ($loop->first)
                                <td class="text-center align-middle"
                                    rowspan="{{ count($sucursalData['movimientos']) }}">
                                    {{ $sucursalData['sucursal'] }}
                                </td>
                            @endif
                            <td class="text-center">{{ $record->fecha_str }}</td>
                            <td class="text-center">{{ $record->movimiento }}</td>
                            <td class="text-center">{{ $record->forma_pago }}</td>
                            <td class="text-center">{{ $record->creado_por }}</td>
                            <td class="text-end">{{ number_format($record->monto, 2) }}</td>
                        </tr>
                    @endforeach

                    {{-- Totalizador por sucursal --}}
                    <tr class="table-success fw-bold">
                        <td class="text-end" colspan="5">{{ __('site.reports.sales_by_department.total') }}
                            {{ $sucursalData['sucursal'] }}</td>
                        <td class="text-end">{{ number_format($sucursalData['total'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">
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
                        <td colspan="5" class="text-end">{{ __('site.reports.sales_by_department.grand_total') }}
                        </td>
                        <td class="text-end">{{ number_format($grandTotal, 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <div class="modal fade" id="pdf-movimientos-caja">
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
