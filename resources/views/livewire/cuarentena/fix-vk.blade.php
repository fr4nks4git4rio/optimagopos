<x-modal>
    <x-slot:title>
        {{ __('site.quarantine.fix.title') }}
    </x-slot:title>

    <x-slot:content>
        <div class="card shadow-sm border-0 mb-4 bg-dark text-white rounded-3 overflow-hidden">
            <div
                class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <span class="badge bg-danger-subtle text-danger text-uppercase px-2 py-1 mb-2 small fw-bold">
                        {{ __('site.quarantine.fix.register_id') }} #{{ $registro->id }}
                    </span>
                    <h2 class="mb-1 fw-bold text-white h4">{{ __('site.quarantine.fix.ticket_repair') }}</h2>
                    <p class="mb-0 text-white-50 small">
                        <i class="bi bi-clock-history me-1"></i> {{ __('site.quarantine.fix.received') }}
                        {{ $registro->created_at->format('d/m/Y H:i:s') }}
                    </p>
                </div>
                <div>
                    <span class="badge bg-warning text-dark px-3 py-1.5 fs-6 shadow-sm">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        {{ __('site.quarantine.fix.pending_repair') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">

                {{-- INFORMACIÓN DEL PROBLEMA --}}
                <div class="card shadow-sm border-0 mb-4 rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-danger-subtle text-danger rounded p-2 me-3"><i
                                    class="bi bi-bug-fill fs-5"></i></div>
                            <h5 class="mb-0 text-dark fw-bold">{{ __('site.quarantine.fix.problem_detail') }}</h5>
                        </div>
                        <div class="alert alert-danger mb-3">
                            <i class="bi bi-exclamation-circle me-1"></i> {{ $registro->texto }}
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <x-input label="{{ __('site.quarantine.fix.origin_ip') }}" model="ip"
                                    class="form-control" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CONTEXTO: CLIENTE / SUCURSAL / TERMINAL --}}
                <div class="card shadow-sm border-0 mb-4 rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary-subtle text-primary rounded p-2 me-3"><i
                                    class="bi bi-diagram-3-fill fs-5"></i></div>
                            <h5 class="mb-0 text-dark fw-bold">{{ __('site.quarantine.fix.ticket_context') }}</h5>
                        </div>
                        <div class="text-muted fs-7 mb-3">
                            <i class="bi bi-info-circle-fill text-warning me-1"></i>
                            {{ __('site.quarantine.fix.ticket_context_info') }}
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <x-select2-component-modals class="form-control"
                                    label="{{ __('site.quarantine.fix.client') }}" :dynamic="true" :lazy="true"
                                    model="cliente_id" :options="$clientesDisponibles" />
                            </div>
                            <div class="col-md-4">
                                <x-select2-component-modals class="form-control"
                                    label="{{ __('site.quarantine.fix.branch') }}" :dynamic="true" :lazy="true"
                                    model="sucursal_id" :options="$sucursalesDisponibles" />
                            </div>
                            <div class="col-md-4">
                                <x-select2-component-modals class="form-control"
                                    label="{{ __('site.quarantine.fix.terminal') }}" :dynamic="true"
                                    :lazy="true" model="terminal_id" :options="$terminalesDisponibles" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TOGGLE MODO AVANZADO --}}
                <div class="card shadow-sm border-0 mb-4 rounded-3">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-code-slash me-2 text-primary"></i>
                                {{ __('site.quarantine.fix.edition_mode') }}
                            </h6>
                            <span class="text-muted fs-7">
                                {{ $modoAvanzado ? __('site.quarantine.fix.json_edition_mode_detail') : __('site.quarantine.fix.form_edition_mode_detail') }}
                            </span>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch"
                                style="width: 3em; height: 1.5em;" wire:model="modoAvanzado"
                                wire:click="toggleModoAvanzado" id="switch-modo-avanzado">
                            <label class="form-check-label fw-bold small ms-2" for="switch-modo-avanzado">
                                {{ $modoAvanzado ? __('site.quarantine.fix.json_edition_mode') : __('site.quarantine.fix.form_edition_mode') }}
                            </label>
                        </div>
                    </div>
                </div>

                @if (!$modoAvanzado)
                    {{-- DATOS GENERALES DEL TICKET --}}
                    <div class="card shadow-sm border-0 mb-4 rounded-3">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary-subtle text-primary rounded p-2 me-3"><i
                                        class="bi bi-receipt fs-5"></i></div>
                                <h5 class="mb-0 text-dark fw-bold">{{ __('site.quarantine.fix.ticket_general_data') }}
                                </h5>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">TerminalId *</label>
                                    <input type="text"
                                        class="form-control @error('formData.TerminalId') is-invalid @enderror"
                                        wire:model="formData.TerminalId">
                                    @error('formData.TerminalId')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">pos *</label>
                                    <input type="text"
                                        class="form-control @error('formData.Data.pos') is-invalid @enderror"
                                        wire:model="formData.Data.pos">
                                    @error('formData.Data.pos')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">operator *</label>
                                    <input type="number"
                                        class="form-control @error('formData.Data.operator') is-invalid @enderror"
                                        wire:model="formData.Data.operator">
                                    @error('formData.Data.operator')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">timestamp *</label>
                                    <input type="text"
                                        class="form-control @error('formData.Data.timestamp') is-invalid @enderror"
                                        wire:model="formData.Data.timestamp">
                                    @error('formData.Data.timestamp')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold text-dark small">OrderStatus *</label>
                                    <select class="form-select @error('formData.Data.OrderStatus') is-invalid @enderror"
                                        wire:model="formData.Data.OrderStatus">
                                        <option value="">{{ __('site.common.select') }}</option>
                                        <option value="1">Open</option>
                                        <option value="2">InProcess</option>
                                        <option value="3">Done</option>
                                        <option value="4">Delayed</option>
                                    </select>
                                    @error('formData.Data.OrderStatus')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold text-dark small">orderNumber *</label>
                                    <input type="text"
                                        class="form-control @error('formData.Data.orderNumber') is-invalid @enderror"
                                        wire:model="formData.Data.orderNumber">
                                    @error('formData.Data.orderNumber')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold text-dark small">table</label>
                                    <input type="text" class="form-control" wire:model="formData.Data.table">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold text-dark small">seat</label>
                                    <input type="text" class="form-control" wire:model="formData.Data.seat">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">location</label>
                                    <input type="text" class="form-control" wire:model="formData.Data.location">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold text-dark small">LocationId</label>
                                    <input type="number" class="form-control" wire:model="formData.Data.LocationId">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">PosIpAddress</label>
                                    <input type="string" class="form-control"
                                        wire:model="formData.Data.PosIpAddress">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold text-dark small">TimeToResolve</label>
                                    <input type="number" step="0.01" class="form-control"
                                        wire:model="formData.Data.TimeToResolve">
                                </div>
                                <div class="col-md-4">
                                    <label
                                        class="form-label fw-bold text-dark small">WarningStatusThresholdInPercent</label>
                                    <input type="number" step="0.01" class="form-control"
                                        wire:model="formData.Data.WarningStatusThresholdInPercent">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ITEMS --}}
                    <div class="card shadow-sm border-0 mb-4 rounded-3">
                        <div
                            class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-dark fw-bold"><i class="bi bi-list-ul me-2 text-primary"></i>
                                {{ __('site.quarantine.fix.ticket_items') }}
                            </h5>
                            <button type="button" class="btn btn-sm btn-success" wire:click="agregarItem">
                                <i class="bi bi-plus-lg me-1"></i> {{ __('site.quarantine.fix.add_item') }}
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div x-data="{
                                newModifier(index, data) {
                                    alert(data);
                                }
                            }" class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead class="table-light">
                                        <tr class="fs-8 text-uppercase text-muted">
                                            <th>name</th>
                                            <th>seat</th>
                                            <th>modifiers</th>
                                            <th>{{ __('site.common.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($items as $indexItem => $item)
                                            <tr wire:key="item-{{ $indexItem }}">
                                                <td>
                                                    <input type="text" class="form-control form-control-sm"
                                                        wire:model="items.{{ $indexItem }}.name">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm"
                                                        wire:model="items.{{ $indexItem }}.seat">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-success mb-1"
                                                        wire:click="agregarModifier('{{ $indexItem }}')">
                                                        <i class="bi bi-plus-lg me-1"></i>
                                                        {{ __('site.quarantine.fix.add_modifier') }}
                                                    </button>
                                                    @if (count($item['modifiers']) > 0)
                                                        <table class="table table-striped table-sm table-condensed">
                                                            <tbody>
                                                                @foreach ($item['modifiers'] as $indexModifier => $mod)
                                                                    <tr>
                                                                        <td class="text-center">
                                                                            <button type="button"
                                                                                class="btn btn-danger btn-sm p-1 py-0"><i
                                                                                    class="bi bi-trash"></i></button>
                                                                        </td>
                                                                        <td>
                                                                            @if ($mod)
                                                                                <span
                                                                                    class="badge bg-primary-subtle text-primary">{{ $mod }}</span>
                                                                            @else
                                                                                <input type="text"
                                                                                    wire:model.lazy="items.{{ $indexItem }}.modifiers.{{ $indexModifier }}"
                                                                                    class="form-control form-control-sm">
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                        wire:click="eliminarItem({{ $indexItem }})">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                        @if (count($items) === 0)
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-3">
                                                    {{ __('site.quarantine.fix.no_items') }}
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            @error('items')
                                <div class="text-danger fs-7 mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                @else
                    {{-- MODO AVANZADO: JSON CRUDO --}}
                    <div class="card shadow-sm border-0 mb-4 rounded-3">
                        <div
                            class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-dark fw-bold"><i class="bi bi-code-slash me-2 text-primary"></i>
                                {{ __('site.quarantine.fix.raw_json') }}
                            </h5>
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                wire:click="formatearJson">
                                <i class="bi bi-text-indent-left me-1"></i> {{ __('site.quarantine.fix.to_format') }}
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <textarea wire:model.lazy="rawJson" rows="20"
                                class="form-control font-monospace @error('rawJson') is-invalid @enderror" style="font-size: 0.8rem;"
                                spellcheck="false"></textarea>
                            @error('rawJson')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="text-muted fs-7 mt-2">
                                <i class="bi bi-info-circle-fill text-warning me-1"></i>
                                {{ __('site.quarantine.fix.raw_json_message') }}
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 rounded-3 sticky-top" style="top: 24px;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 text-uppercase fw-bold text-secondary fs-7">
                            <i class="bi bi-check2-circle me-2 text-primary"></i> {{ __('site.common.actions') }}
                        </h6>
                    </div>
                    <div class="card-body p-4">

                        <div class="alert alert-secondary fs-7 mb-3">
                            <i class="bi bi-eye me-1"></i> {{ __('site.quarantine.fix.json_preview') }}
                        </div>

                        <pre class="bg-dark text-white p-3 rounded-3 fs-8 mb-3" style="max-height: 300px; overflow: auto;">{{ $this->previewJson }}</pre>

                    </div>
                </div>
            </div>
        </div>
        @if ($confirmDescartarRegistroClass)
            <div class="modal {{ $confirmDescartarRegistroClass }}">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('site.quarantine.fix.delete_confirm') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                wire:click="$set('confirmDescartarRegistroClass', '')"></button>
                        </div>
                        <div class="modal-body pb-0 text-center">
                            <x-alert :alert="'danger'" icon="exclamation-octagon">
                                {!! __('site.quarantine.fix.delete_are_you_sure') !!}
                            </x-alert>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                wire:click="$set('confirmDescartarRegistroClass', '')">{{ __('site.common.close') }}</button>
                            <button type="button" class="btn btn-danger"
                                wire:click="descartar">{{ __('site.common.delete') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
            wire:click="$emit('closeModal')">{{ __('site.common.close') }}</button>

        <button type="button" class="btn btn-primary" wire:click="guardar" wire:loading.attr="disabled"
            wire:target="guardar">
            <span wire:loading wire:target="guardar" class="spinner-border spinner-border-sm me-2"></span>
            {{ __('site.quarantine.fix.save_fix') }}
        </button>

        <button type="button" class="btn btn-success" wire:click="guardarYReprocesar" wire:loading.attr="disabled"
            wire:target="guardarYReprocesar">
            <span wire:loading wire:target="guardarYReprocesar" class="spinner-border spinner-border-sm me-2"></span>
            {{ __('site.quarantine.fix.save_and_process') }}
        </button>

        <button type="button" wire:click="showDescartarRegistroModal" class="btn btn-danger">
            {{ __('site.quarantine.fix.delete_register') }}
        </button>
    </x-slot:buttons>
</x-modal>
