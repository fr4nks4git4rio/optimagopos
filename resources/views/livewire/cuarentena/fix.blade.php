<x-modal form-action="delete">
    <x-slot:title>
        Reparar Ticket en cuarentena
    </x-slot:title>

    <x-slot:content>
        <div class="card shadow-sm border-0 mb-4 bg-dark text-white rounded-3 overflow-hidden">
            <div
                class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <span class="badge bg-danger-subtle text-danger text-uppercase px-2 py-1 mb-2 small fw-bold">
                        Registro en Cuarentena #{{ $registro->id }}
                    </span>
                    <h2 class="mb-1 fw-bold text-white h4">Reparación de Ticket</h2>
                    <p class="mb-0 text-white-50 small">
                        <i class="bi bi-clock-history me-1"></i> Recibido el
                        {{ $registro->created_at->format('d/m/Y H:i:s') }}
                    </p>
                </div>
                <div>
                    <span class="badge bg-warning text-dark px-3 py-1.5 fs-6 shadow-sm">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Pendiente de Reparar
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
                            <h5 class="mb-0 text-dark fw-bold">Detalle del Problema</h5>
                        </div>
                        <div class="alert alert-danger mb-3">
                            <i class="bi bi-exclamation-circle me-1"></i> {{ $registro->texto }}
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <x-input :label="'IP de Origen'" model="ip" class="form-control" />
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
                            <h5 class="mb-0 text-dark fw-bold">Contexto del Ticket</h5>
                        </div>
                        <div class="text-muted fs-7 mb-3">
                            <i class="bi bi-info-circle-fill text-warning me-1"></i>
                            Completa esta información si no se pudo determinar automáticamente al recibir el ticket.
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <x-select2-component-modals class="form-control" :label="'Cliente'" :dynamic="true"
                                    :lazy="true" model="cliente_id" :options="$clientesDisponibles" />
                            </div>
                            <div class="col-md-4">
                                <x-select2-component-modals class="form-control" :label="'Sucursal'" :dynamic="true"
                                    :lazy="true" model="sucursal_id" :options="$sucursalesDisponibles" />
                            </div>
                            <div class="col-md-4">
                                <x-select2-component-modals class="form-control" :label="'Terminal'" :dynamic="true"
                                    :lazy="true" model="terminal_id" :options="$terminalesDisponibles" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TOGGLE MODO AVANZADO --}}
                <div class="card shadow-sm border-0 mb-4 rounded-3">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-code-slash me-2 text-primary"></i>Modo de
                                Edición</h6>
                            <span class="text-muted fs-7">
                                {{ $modoAvanzado ? 'Editando el JSON crudo directamente.' : 'Editando mediante formulario estructurado.' }}
                            </span>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch"
                                style="width: 3em; height: 1.5em;" wire:model="modoAvanzado"
                                wire:click="toggleModoAvanzado" id="switch-modo-avanzado">
                            <label class="form-check-label fw-bold small ms-2" for="switch-modo-avanzado">
                                {{ $modoAvanzado ? 'Avanzado (JSON)' : 'Formulario' }}
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
                                <h5 class="mb-0 text-dark fw-bold">Datos Generales del Ticket</h5>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">PosId *</label>
                                    <input type="text"
                                        class="form-control @error('formData.PosId') is-invalid @enderror"
                                        wire:model="formData.PosId">
                                    @error('formData.PosId')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">TransactionId *</label>
                                    <input type="text"
                                        class="form-control @error('formData.TransactionId') is-invalid @enderror"
                                        wire:model="formData.TransactionId">
                                    @error('formData.TransactionId')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">ClerkId</label>
                                    <input type="text" class="form-control" wire:model="formData.ClerkId">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">ClerkName</label>
                                    <input type="text" class="form-control" wire:model="formData.ClerkName">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">FiscalInvoice</label>
                                    <select class="form-select" wire:model="formData.FiscalInvoice">
                                        <option value="Si">Sí</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">TransactionStartTime</label>
                                    <input type="text"
                                        class="form-control @error('formData.TransactionStartTime') is-invalid @enderror"
                                        wire:model="formData.TransactionStartTime" placeholder="dd/mm/yyyy HH:mm:ss">
                                    @error('formData.TransactionStartTime')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">TransactionEndTime</label>
                                    <input type="text"
                                        class="form-control @error('formData.TransactionEndTime') is-invalid @enderror"
                                        wire:model="formData.TransactionEndTime" placeholder="dd/mm/yyyy HH:mm:ss">
                                    @error('formData.TransactionEndTime')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">APIUserName</label>
                                    <input type="text" class="form-control" wire:model="formData.APIUserName">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">MerchantFiscalId</label>
                                    <input type="text" class="form-control"
                                        wire:model="formData.MerchantFiscalId">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ITEMS --}}
                    <div class="card shadow-sm border-0 mb-4 rounded-3">
                        <div
                            class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-dark fw-bold"><i class="bi bi-list-ul me-2 text-primary"></i>Items
                                del Ticket</h5>
                            <button type="button" class="btn btn-sm btn-success" wire:click="agregarItem">
                                <i class="bi bi-plus-lg me-1"></i> Agregar Item
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead class="table-light">
                                        <tr class="fs-8 text-uppercase text-muted">
                                            <th style="width: 90px;">Type</th>
                                            <th>Name</th>
                                            <th style="width: 80px;">SKU</th>
                                            <th style="width: 70px;">Qty</th>
                                            <th style="width: 90px;">Amount</th>
                                            <th style="width: 70px;">Tip</th>
                                            <th style="width: 90px;">Discount</th>
                                            <th style="width: 110px;">DepartmentId</th>
                                            <th style="width: 40px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($items as $index => $item)
                                            <tr wire:key="item-{{ $index }}">
                                                <td>
                                                    <select class="form-select form-select-sm"
                                                        wire:model="items.{{ $index }}.Type">
                                                        <option value="Product">Product</option>
                                                        <option value="Tender">Tender</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm"
                                                        wire:model="items.{{ $index }}.Name">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm"
                                                        wire:model="items.{{ $index }}.SKU">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm"
                                                        wire:model="items.{{ $index }}.Qty">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01"
                                                        class="form-control form-control-sm"
                                                        wire:model="items.{{ $index }}.Amount">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01"
                                                        class="form-control form-control-sm"
                                                        wire:model="items.{{ $index }}.Tip">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01"
                                                        class="form-control form-control-sm"
                                                        wire:model="items.{{ $index }}.Discount">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm"
                                                        wire:model="items.{{ $index }}.DepartmentId">
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                        wire:click="eliminarItem({{ $index }})">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                        @if (count($items) === 0)
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-3">
                                                    No hay items. Usa "Agregar Item" para incluir uno.
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
                            <h5 class="mb-0 text-dark fw-bold"><i class="bi bi-code-slash me-2 text-primary"></i>JSON
                                Crudo</h5>
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                wire:click="formatearJson">
                                <i class="bi bi-text-indent-left me-1"></i> Formatear
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
                                Edita el JSON directamente. Al guardar se validará que sea sintácticamente correcto.
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 rounded-3 sticky-top" style="top: 24px;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 text-uppercase fw-bold text-secondary fs-7">
                            <i class="bi bi-check2-circle me-2 text-primary"></i> Acciones
                        </h6>
                    </div>
                    <div class="card-body p-4">

                        <div class="alert alert-secondary fs-7 mb-3">
                            <i class="bi bi-eye me-1"></i> Vista previa de cómo quedará el JSON final antes de guardar.
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
                            <h5 class="modal-title">Confirmar eliminación</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                wire:click="$set('confirmDescartarRegistroClass', '')"></button>
                        </div>
                        <div class="modal-body pb-0 text-center">
                            <x-alert :alert="'danger'" icon="exclamation-octagon">
                                ¿Seguro que deseas descartar este registro de cuarentena? <b>Esta acción no se puede
                                    deshacer.</b>
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
            wire:click="$emit('closeModal')">Cerrar</button>

        <button type="button" class="btn btn-primary" wire:click="guardar" wire:loading.attr="disabled"
            wire:target="guardar">
            <span wire:loading wire:target="guardar" class="spinner-border spinner-border-sm me-2"></span>
            Guardar Reparación
        </button>

        <button type="button" class="btn btn-success" wire:click="guardarYReprocesar" wire:loading.attr="disabled"
            wire:target="guardarYReprocesar">
            <span wire:loading wire:target="guardarYReprocesar" class="spinner-border spinner-border-sm me-2"></span>
            Guardar y Procesar
        </button>

        <button type="button" wire:click="showDescartarRegistroModal" class="btn btn-danger">Eliminar
            Registro</button>
    </x-slot:buttons>
</x-modal>
