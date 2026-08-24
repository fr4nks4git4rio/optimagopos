@section('title', 'Artículos Vendidos')

<div>
    <div wire:loading.delay.longer>
        <div class="loading">
            <img src="{{ asset('img/loading.gif') }}" />
        </div>
    </div>

    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row justify-content-between">
        <div class="row mb-1 col-md-10">
            <div class="col-sm-2">
                <x-input label="{{ __('site.reports.articles_sold.start_date') }}" type="date" :lazy="true"
                    model="fechaInicio" />
            </div>
            <div class="col-sm-2">
                <x-input label="{{ __('site.reports.articles_sold.end_date') }}" type="date" :lazy="true"
                    model="fechaFin" />
            </div>
            <div class="col-sm-8">
                <x-select2-multiple label="{{ __('site.reports.articles_sold.branch') }}"
                    placeholder="{{ __('site.common.select') }}..." class="form-control" :options="$sucursalesAll"
                    model="sucursal" :lazy="true" :dynamic="true" />
            </div>
        </div>
        <div class="mb-1 col-md-2 text-end">
            @can('reportsArticlesSold-print')
                <button type="button" class="btn btn-site-primary mr-1" wire:click="imprimirPdf()">
                    <span class="bi bi-file-pdf"></span>
                    {{ __('site.common.print') }}
                </button>
            @endcan
            @can('reportsArticlesSold-export')
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
                        <th class="text-center align-middle" rowspan="2" style="white-space: nowrap !important">
                            {{ __('site.reports.articles_sold.article') }}
                        </th>
                        @foreach ($sucursales as $sucursal)
                            <th class="text-center" colspan="2" style="white-space: nowrap !important">
                                {{ $sucursal }}
                            </th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach ($sucursales as $sucursal)
                            <th class="text-end" style="white-space: nowrap !important">
                                {{ __('site.reports.articles_sold.amount') }}</th>
                            <th class="text-center" style="white-space: nowrap !important">
                                {{ __('site.reports.articles_sold.quantity') }}</th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td class="text-center align-middle">
                            {{ $record->producto }}
                        </td>
                        @foreach ($sucursales as $i => $sucursal)
                            @php $celda = $record->montos[$i] ?? ['monto' => 0, 'vendidos' => 0]; @endphp
                            <td class="text-end">{{ number_format($celda['monto'], 2) }}</td>
                            <td class="text-center">{{ $celda['vendidos'] }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 1 + count($sucursales) * 2 }}" class="text-center">
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
                        <td class="text-end">{{ __('site.reports.articles_sold.general_total') }}</td>
                        @foreach ($sucursales as $i => $sucursal)
                            @php $totalGeneral = $grandTotal[$i] ?? ['monto' => 0, 'vendidos' => 0]; @endphp
                            <td class="text-end">{{ number_format($totalGeneral['monto'], 2) }}</td>
                            <td class="text-center">{{ $totalGeneral['vendidos'] }}</td>
                        @endforeach
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <div class="modal fade" id="pdf-articulos-vendidos">
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
