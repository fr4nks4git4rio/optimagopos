<x-modal form-action="save">
    <x-slot:title>
        {{ $modulo->exists ? 'Editar ' : 'Crear ' }}Módulo
    </x-slot:title>

    <x-slot:content>
        <div wire:init="init" class="container py-4">
            <div class="row g-4">

                <div class="col-md-7">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nombre del Módulo *</label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                    wire:model="nombre" placeholder="Ej. Facturación Electrónica">
                                @error('nombre')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Descripción</label>
                                <textarea class="form-control @error('descripcion') is-invalid @enderror" wire:model="descripcion" rows="3"
                                    placeholder="Breve resumen de los alcances del módulo..."></textarea>
                                @error('descripcion')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label fw-bold">Ícono (Class de Bootstrap) *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i
                                                class="bi {{ $icono }}"></i></span>
                                        <input type="text" class="form-control @error('icono') is-invalid @enderror"
                                            wire:model="icono" placeholder="Ej. bi-currency-dollar">
                                    </div>
                                    <small class="text-muted d-block mt-1">Usa clases de <a
                                            href="https://icons.getbootstrap.com/" target="_blank">Bootstrap
                                            Icons</a></small>
                                    @error('icono')
                                        <small class="text-danger d-block">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-sm-6 mb-3">
                                    <label class="form-label fw-bold">Color del Ícono *</label>
                                    <div class="d-flex align-items-center">
                                        <input type="color" class="form-control form-control-color me-2"
                                            wire:model="icono_color" title="Elige un color">
                                        <input type="text"
                                            class="form-control form-control-sm @error('icono_color') is-invalid @enderror"
                                            wire:model="icono_color" placeholder="#000000">
                                    </div>
                                    @error('icono_color')
                                        <small class="text-danger d-block">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label fw-bold">Cantidad de Funciones *</label>
                                    <input type="number"
                                        class="form-control @error('cant_funciones') is-invalid @enderror"
                                        wire:model="cant_funciones" min="1">
                                    @error('cant_funciones')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-sm-6 mb-3">
                                    <label class="form-label fw-bold">Costo Base ($) *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01"
                                            class="form-control @error('costo_base') is-invalid @enderror"
                                            wire:model="costo_base" min="0">
                                    </div>
                                    @error('costo_base')
                                        <small class="text-danger d-block">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                        <div class="card-header bg-light py-3">
                            <h6 class="mb-0 text-muted uppercase fw-bold"
                                style="font-size: 0.8rem; letter-spacing: 1px;">Vista Previa del Módulo</h6>
                        </div>
                        <div class="card-body p-4 text-center">

                            <div class="mx-auto rounded-3 d-flex align-items-center justify-content-center mb-3 shadow-sm"
                                style="width: 80px; height: 80px; background-color: {{ $icono_color }};">
                                <i class="bi {{ $icono }}"
                                    style="font-size: 2.2rem; color: #fff;"></i>
                            </div>

                            <h4 class="fw-bold text-dark text-truncate">{{ $nombre ? $nombre : 'Nombre del Módulo' }}
                            </h4>
                            <p class="text-muted small mb-4" style="min-height: 40px;">
                                {{ $descripcion ? $descripcion : 'Aquí aparecerá la descripción introducida para el módulo...' }}
                            </p>

                            <div class="row g-2 border-top pt-3">
                                <div class="col-6 border-end">
                                    <small class="text-muted d-block">Funciones</small>
                                    <span class="fs-5 fw-bold text-dark">{{ $cant_funciones ?: 0 }}</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Costo Base</small>
                                    <span
                                        class="fs-5 fw-bold text-success">${{ number_format((float) $costo_base, 2) }}</span>
                                </div>
                            </div>

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
        <button type="submit" class="btn btn-primary">Guardar Módulo</button>
    </x-slot:buttons>
</x-modal>
