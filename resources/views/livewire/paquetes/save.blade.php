<x-modal form-action="save">
    <x-slot:title>
        {{ $paquete->exists ? 'Editar ' : 'Crear ' }}Paquete
    </x-slot:title>

    <x-slot:content>
        <div wire:init="init" class="container py-4">
            <div class="row g-4">

                <div class="col-md-7">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nombre del Paquete *</label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                    wire:model.defer="nombre" placeholder="Ej. Paquete Básico">
                                @error('nombre')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Descripción</label>
                                <textarea class="form-control @error('descripcion') is-invalid @enderror" wire:model.defer="descripcion" rows="2"
                                    placeholder="Detalla qué incluye esta combinación de módulos..."></textarea>
                                @error('descripcion')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Precio Especial de Venta ($) *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01"
                                        class="form-control @error('precio') is-invalid @enderror" wire:model="precio"
                                        min="0">
                                </div>
                                @error('precio')
                                    <small class="text-danger d-block">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="row bg-light rounded p-3 mb-4 g-3 border mx-0">
                                <div class="col-sm-4 mt-0">
                                    <label class="form-label fw-bold text-secondary small"><i
                                            class="bi bi-building me-1"></i>Sucursales Permitidas *</label>
                                    <input type="number"
                                        class="form-control @error('cant_sucursales') is-invalid @enderror"
                                        wire:model="cant_sucursales" min="1">
                                    @error('cant_sucursales')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-sm-4 mt-0">
                                    <label class="form-label fw-bold text-secondary small"><i
                                            class="bi bi-display me-1"></i>Terminales / Cajas *</label>
                                    <input type="number"
                                        class="form-control @error('cant_terminales') is-invalid @enderror"
                                        wire:model="cant_terminales" min="1">
                                    @error('cant_terminales')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-sm-4 mt-0">
                                    <label class="form-label fw-bold text-secondary small"><i
                                            class="bi bi-person me-1"></i>Usuarios Permitidos *</label>
                                    <input type="number"
                                        class="form-control @error('cant_usuarios') is-invalid @enderror"
                                        wire:model="cant_usuarios" min="1">
                                    @error('cant_usuarios')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0">Seleccionar Módulos Integrados *</label>
                                    <span
                                        class="badge {{ count($modulos) >= 2 ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ count($modulos) }} seleccionados (Mín. 2)
                                    </span>
                                </div>

                                @error('modulos')
                                    <div class="alert alert-danger py-2 small mb-3 d-block">{{ $message }}</div>
                                @enderror

                                <div class="row g-2" style="max-height: 350px; overflow-y: auto;">
                                    @forelse($availableModules as $module)
                                        <div class="col-12">
                                            <label
                                                class="card h-100 border p-3 style-clickable-card {{ in_array($module->id, $modulos) ? 'border-primary bg-light-subtle' : '' }}"
                                                style="cursor: pointer;">
                                                <div class="d-flex align-items-center">
                                                    <div class="form-check me-3">
                                                        @if (in_array($module->id, [1, 2]))
                                                            <input class="form-check-input" type="checkbox"
                                                                value="{{ $module->id }}" wire:model="modulos"
                                                                id="mod-{{ $module->id }}" disabled>
                                                        @else
                                                            <input class="form-check-input" type="checkbox"
                                                                value="{{ $module->id }}" wire:model="modulos"
                                                                id="mod-{{ $module->id }}">
                                                        @endif
                                                    </div>

                                                    <div class="rounded p-2 text-center me-3"
                                                        style="background-color: {{ $module->icono_color }}; width: 45px;">
                                                        <i class="bi {{ $module->icono }}"
                                                            style="color: #fff; font-size: 1.2rem;"></i>
                                                    </div>

                                                    <div class="flex-grow-1 text-truncate">
                                                        <h6 class="mb-0 fw-bold text-dark text-truncate">
                                                            {{ $module->nombre }}</h6>
                                                        <small
                                                            class="text-muted d-block text-truncate">{{ $module->descripcion ?? 'Sin descripción.' }}</small>
                                                    </div>

                                                    <div class="text-end ms-2">
                                                        <span
                                                            class="badge bg-secondary-subtle text-secondary border">${{ number_format($module->costo_base, 2) }}</span>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    @empty
                                        <div class="col-12 text-center py-4">
                                            <p class="text-muted mb-0"><i class="bi bi-exclamation-circle me-2"></i>No
                                                hay módulos
                                                registrados. Crea módulos primero.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                        <div class="card-header bg-dark text-white py-3">
                            <h6 class="mb-0 text-uppercase fw-bold style-letter-spacing" style="font-size: 0.8rem;">
                                Desglose Comercial</h6>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="fw-bold text-dark">{{ $nombre ?: 'Nombre del Paquete' }}</h5>
                            <p class="text-muted small border-bottom pb-3">
                                {{ $descripcion ?: 'El resumen de tu paquete aparecerá aquí...' }}</p>

                            @php
                                $sumBaseCost = $availableModules->whereIn('id', $modulos)->sum('costo_base');
                                $saving = $sumBaseCost - (float) $precio;
                            @endphp

                            <ul class="list-group list-group-flush mb-4">
                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                    <span class="text-muted">Suma de costos base individuales:</span>
                                    <span class="fw-bold text-dark">${{ number_format($sumBaseCost, 2) }}</span>
                                </li>
                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                    <span class="text-muted">Precio del paquete:</span>
                                    <span
                                        class="fw-bold text-primary fs-5">${{ number_format((float) $precio, 2) }}</span>
                                </li>

                                @if (count($modulos) >= 2 && $saving > 0)
                                    <li
                                        class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent border-top border-dashed">
                                        <span class="text-success fw-bold"><i class="bi bi-piggy-bank me-1"></i>
                                            Ahorro
                                            para el cliente:</span>
                                        <span
                                            class="badge bg-success-subtle text-success fs-6 fw-bold">${{ number_format($saving, 2) }}</span>
                                    </li>
                                @endif
                            </ul>

                            @if (count($modulos) < 2)
                                <div class="alert alert-warning d-flex align-items-center small m-0" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5 text-warning"></i>
                                    <div>
                                        Requiere al menos <strong>2 módulos específicos</strong> seleccionados para
                                        habilitar la creación de este paquete.
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-success d-flex align-items-center small m-0" role="alert">
                                    <i class="bi bi-check-circle-fill me-2 fs-5 text-success"></i>
                                    <div>
                                        Estructura de paquete válida. Listo para procesarse.
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$emit('closeModal')">
            Cerrar
        </button>
        <button type="submit" class="btn btn-primary">Guardar Paquete</button>
    </x-slot:buttons>
</x-modal>
