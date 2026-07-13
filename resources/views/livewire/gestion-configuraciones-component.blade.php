@section('title', __('site.system_configs.title'))

<div>
    <h1 class="fs-1 mb-2">@yield('title')</h1>
    <div class="py-1">
        <div class="row justify-content-center">
            <div class="col-12">

                <div class="card shadow-sm border-0">
                    <div
                        class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <span class="badge bg-secondary-subtle text-secondary border">{{ __('site.system_configs.control_panel') }}</span>
                    </div>

                    <div class="card-body p-4">

                        <h6 class="text-uppercase text-muted fw-bold mb-3"
                            style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            <i class="bi bi-shield-check me-1"></i> {{ __('site.system_configs.technical_fiscal_parameters') }}
                        </h6>

                        <div class="row g-3 mb-4 p-3 bg-light rounded border">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">{{ __('site.system_configs.stamping_method') }}</label>
                                <select class="form-select @error('cfdi_timbrado_productivo') is-invalid @enderror"
                                    wire:model="cfdi_timbrado_productivo">
                                    <option value="0">{{ __('site.system_configs.testing') }}</option>
                                    <option value="1">{{ __('site.system_configs.production') }}</option>
                                </select>
                                @error('cfdi_timbrado_productivo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small">{{ __('site.system_configs.iva_percent') }}</label>
                                <div class="input-group">
                                    <input type="number" step="0.01"
                                        class="form-control @error('iva') is-invalid @enderror" wire:model="iva"
                                        min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                                @error('iva')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small">{{ __('site.system_configs.base_currency') }}</label>
                                <select class="form-select @error('moneda_sistema') is-invalid @enderror"
                                    wire:model="moneda_sistema">
                                    @foreach ($monedas as $index => $moneda)
                                        <option value="{{ $moneda }}">{{ $moneda }} - {{ $index }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('moneda_sistema')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <h6 class="text-uppercase text-muted fw-bold mb-3"
                            style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            <i class="bi bi-currency-dollar me-1"></i> {{ __('site.system_configs.price_list_surplus_items') }}
                        </h6>

                        <div class="row g-3 mb-4 p-3 bg-light rounded border">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">{{ __('site.system_configs.additional_branch') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ $moneda_sistema }}</span>
                                    <input type="number" step="0.01"
                                        class="form-control @error('precio_sucursal_adicional') is-invalid @enderror"
                                        wire:model="precio_sucursal_adicional" min="0">
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                                    {{ __('site.system_configs.periodic_unitary_cost') }}
                                </small>
                                @error('precio_sucursal_adicional')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small">{{ __('site.system_configs.additional_terminal') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ $moneda_sistema }}</span>
                                    <input type="number" step="0.01"
                                        class="form-control @error('precio_terminal_adicional') is-invalid @enderror"
                                        wire:model="precio_terminal_adicional" min="0">
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                                    {{ __('site.system_configs.periodic_unitary_cost') }}
                                </small>
                                @error('precio_terminal_adicional')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small">{{ __('site.system_configs.additional_user') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ $moneda_sistema }}</span>
                                    <input type="number" step="0.01"
                                        class="form-control @error('precio_usuario_adicional') is-invalid @enderror"
                                        wire:model="precio_usuario_adicional" min="0">
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                                    {{ __('site.system_configs.periodic_unitary_cost') }}
                                </small>
                                @error('precio_usuario_adicional')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>

                <div class="alert alert-info border-0 shadow-sm mt-3 d-flex align-items-center small" role="alert">
                    <i class="bi bi-info-circle-fill me-3 fs-4 text-primary"></i>
                    <div>
                        {!! __('site.system_configs.operative_info') !!}
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
