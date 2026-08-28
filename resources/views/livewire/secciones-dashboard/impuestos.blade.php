@can('dashboardResume-viewTaxes' && count($impuestosData['impuestos']) > 0)
    <div class="row g-3 mb-3 px-1">
        @foreach ($impuestosData['impuestos'] as $impuesto)
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card border-0 border-start border-danger bg-dark-subtle shadow-sm border-4 text-center h-100">
                    <div class="card-body align-items-center d-flex flex-column">
                        <span class="fs-5 fw-bold text-uppercase">{{ $impuesto->nombre }}</span>
                        <span class="fs-3 text-danger m-auto">
                            {{ __('site.dashboard.taxable') }} -> {{ $impuesto->gravable }}</span>
                        <span class="fs-3 text-danger m-auto">
                            {{ __('site.dashboard.tax') }} -> {{ $impuesto->impuesto }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endcan
