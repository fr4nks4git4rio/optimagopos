@section('title', 'Gestión de Subscripción de Cliente')

<div>
    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="card shadow-sm border-0 mb-4 bg-dark text-white">
        <div class="card-body p-4 d-flex align-items-center justify-content-between">
            <div>
                <span class="badge bg-primary mb-2">Asignación Contractual de Recursos</span>
                <h3 class="mb-1 fw-bold">
                    {{ $cliente ? Illuminate\Support\Facades\Crypt::decrypt($cliente->nombre_comercial) : '' }}</h3>
                @if ($cliente)
                    <p class="mb-0 text-info small">
                        <i class="bi bi-building me-1"></i> Identificador Cliente #{{ $cliente->id }}
                    </p>
                @endif
            </div>
            <div class="text-end">
                @php
                    $classEstado = 'dark';
                    switch ($estado) {
                        case 'PENDIENTE':
                            $classEstado = 'primary';
                            break;
                        case 'ACTIVA':
                            $classEstado = 'success';
                            break;
                        case 'VENCIDA':
                            $classEstado = 'warning';
                            break;
                        case 'REVOCADA':
                            $classEstado = 'danger';
                            break;
                    }
                @endphp
                <span
                    class="badge bg-{{ $classEstado }} }} fs-6">
                    {{ $estado }}
                </span><br>
                <span class="badge {{ $paquete_id ? 'bg-success' : 'bg-warning text-dark' }} fs-6 mt-1 shadow-sm">
                    {{ $paquete_id ? 'Suscripción por Paquete' : 'Suscripción Personalizada' }}
                </span>
            </div>
        </div>
    </div>

    <form wire:submit.prevent="submit">
        <div class="row g-4">

            <div class="col-md-7">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 text-dark fw-bold"><i class="bi bi-sliders me-2 text-primary"></i>Configuración
                            de la Subscripción</h5>
                    </div>
                    <div class="card-body p-4">

                        @if (!$suscripcion || !$suscripcion->id)
                            <div class="mb-4 p-3 bg-light rounded border">
                                <label class="form-label fw-bold text-secondary small">Seleccione el Cliente:</label>
                                <x-select2 :dynamic="true" :lazy="true" model="cliente_id" :options="$clientes"
                                    class="form-control" />
                                <span class="text-muted small"><i class="bi bi-info-circle"> Solo se mostrarán los
                                        clientes que no tengan suscripciones <strong>pendientes</strong> o
                                        <strong>activas</strong> y que tengan todos sus datos fiscales al día.</i> </span>
                            </div>
                        @endif

                        <div class="mb-4 p-3 bg-light rounded border">
                            <label class="form-label fw-bold text-secondary small">Vincular a un Paquete
                                Base:</label>
                            <select class="form-select bg-white" wire:model="paquete_id">
                                <option value="">-- Ninguno / Crear Plan Custom desde cero --</option>
                                @foreach ($paquetes as $pkg)
                                    <option value="{{ $pkg->id }}">{{ $pkg->nombre }}
                                        (${{ number_format($pkg->precio, 2) }}
                                        {{ $globalSettings['moneda_sistema'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <h6 class="text-uppercase text-muted fw-bold mb-3"
                            style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            <i class="bi bi-calendar-check me-1"></i> Vigencias y Ciclo de Cobro
                        </h6>
                        <div class="row g-3 mb-4 p-3 bg-light rounded border mx-0 text-dark">
                            <div class="col-md-6 mt-0">
                                <label class="form-label fw-bold small text-primary-dark"><i
                                        class="bi bi-play-circle me-1"></i>Inicio de Operaciones *</label>
                                <input type="date"
                                    class="form-control @error('fecha_inicio_operaciones') is-invalid @enderror"
                                    wire:model="fecha_inicio_operaciones">
                                @error('fecha_inicio_operaciones')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mt-0">
                                <label class="form-label fw-bold small text-primary-dark"><i
                                        class="bi bi-credit-card-2-front me-1"></i>Próxima Fecha de Cobro *</label>
                                <input type="date"
                                    class="form-control @error('fecha_inicio_pagos') is-invalid @enderror"
                                    wire:model="fecha_inicio_pagos">
                                @error('fecha_inicio_pagos')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mt-3">
                                <label class="form-label fw-bold small text-primary-dark"><i
                                        class="bi bi-arrow-repeat me-1"></i>Periodicidad del Cobro *</label>
                                <select class="form-select @error('periodicidad_pagos') is-invalid @enderror"
                                    wire:model="periodicidad_pagos">
                                    <option value="MENSUAL">Mensual</option>
                                    <option value="SEMESTRAL">Semestral</option>
                                    <option value="ANUAL">Anual</option>
                                </select>
                                @error('periodicidad_pagos')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <h6 class="text-uppercase text-muted fw-bold mb-3"
                            style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            <i class="bi bi-cpu"></i> Capacidades de Infraestructura
                        </h6>
                        <div class="row g-3 mb-4 p-3 bg-light rounded border mx-0 text-dark">
                            <div class="col-md-4 mt-0">
                                <label class="form-label fw-bold small">Sucursales</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-building"></i></span>
                                    <input type="number"
                                        class="form-control
                                    @error('cant_sucursales') is-invalid @enderror"
                                        @if ($paquete_id) disabled @endif wire:model="cant_sucursales"
                                        min="1">
                                    @if ($paquete_id)
                                        <span wire:click="incrementSucursales"
                                            class="input-group-text bg-success text-white cursor-pointer">
                                            <i class="bi bi-plus-circle"></i></span>
                                        <span wire:click="resetSucursales"
                                            class="input-group-text bg-warning text-black cursor-pointer">
                                            <i class="bi bi-arrow-clockwise"></i></span>
                                        <span wire:click="decrementSucursales"
                                            class="input-group-text bg-danger text-white cursor-pointer">
                                            <i class="bi bi-dash-circle"></i></span>
                                    @endif
                                </div>
                                @error('cant_sucursales')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mt-0">
                                <label class="form-label fw-bold small">Terminales</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-display"></i></span>
                                    <input type="number"
                                        class="form-control @error('cant_terminales') is-invalid @enderror"
                                        @if ($paquete_id) disabled @endif wire:model="cant_terminales"
                                        min="1">
                                    @if ($paquete_id)
                                        <span wire:click="incrementTerminales"
                                            class="input-group-text bg-success text-white cursor-pointer">
                                            <i class="bi bi-plus-circle"></i></span>
                                        <span wire:click="resetTerminales"
                                            class="input-group-text bg-warning text-black cursor-pointer">
                                            <i class="bi bi-arrow-clockwise"></i></span>
                                        <span wire:click="decrementTerminales"
                                            class="input-group-text bg-danger text-white cursor-pointer">
                                            <i class="bi bi-dash-circle"></i></span>
                                    @endif
                                </div>
                                @error('cant_terminales')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mt-0">
                                <label class="form-label fw-bold small">Usuarios</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-people"></i></span>
                                    <input type="number"
                                        class="form-control @error('cant_usuarios') is-invalid @enderror"
                                        @if ($paquete_id) disabled @endif wire:model="cant_usuarios"
                                        min="1">
                                    @if ($paquete_id)
                                        <span wire:click="incrementUsuarios"
                                            class="input-group-text bg-success text-white cursor-pointer">
                                            <i class="bi bi-plus-circle"></i></span>
                                        <span wire:click="resetUsuarios"
                                            class="input-group-text bg-warning text-black cursor-pointer">
                                            <i class="bi bi-arrow-clockwise"></i></span>
                                        <span wire:click="decrementUsuarios"
                                            class="input-group-text bg-danger text-white cursor-pointer">
                                            <i class="bi bi-dash-circle"></i></span>
                                    @endif
                                </div>
                                @error('cant_usuarios')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div
                        class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-dark fw-bold"><i
                                class="bi bi-layers me-2 text-primary"></i>Aprovisionamiento de Módulos</h5>
                        <span class="badge bg-dark">{{ count($modulos) }} Seleccionados</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-2" style="max-height: 320px; overflow-y: auto;">
                            @foreach ($modulosDisponibles as $module)
                                <div class="col-12">
                                    <label
                                        class="card border p-3 h-100 {{ in_array($module->id, $modulos) ? 'border-primary bg-light-subtle' : '' }}"
                                        style="cursor: pointer;">
                                        <div class="d-flex align-items-center">
                                            <div class="form-check form-switch me-3">
                                                <input class="form-check-input" type="checkbox" role="switch"
                                                    value="{{ $module->id }}" wire:model="modulos"
                                                    @if ($paquete_id && in_array($module->id, $this->modulos_paquete)) disabled @endif
                                                    id="mod-{{ $module->id }}">
                                            </div>

                                            <div class="rounded p-2 text-center me-3"
                                                style="background-color: {{ $module->icono_color }}; width: 40px; height: 40px;">
                                                <i class="bi {{ $module->icono }}"
                                                    style="color: #fff; font-size: 1.1rem;"></i>
                                            </div>

                                            <div class="flex-grow-1 text-truncate">
                                                <h6 class="mb-0 fw-bold text-dark text-truncate">
                                                    {{ $module->nombre }}</h6>
                                                <small class="text-muted d-block text-truncate"
                                                    style="font-size: 0.75rem;">
                                                    Costo Base Individual:
                                                    ${{ number_format($module->costo_base, 2) }}
                                                    {{ $globalSettings['moneda_sistema'] }}
                                                </small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                    <div class="card-header bg-secondary text-white py-3">
                        <h6 class="mb-0 text-uppercase fw-bold" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                            Resumen Económico y Plazo</h6>
                    </div>
                    <div class="card-body p-4">

                        <div class="text-center mb-4 bg-light rounded p-3 border">
                            <span class="text-muted small d-block text-uppercase fw-bold"
                                style="font-size: 0.65rem;">Costo Recurrente {{ $periodicidad_pagos }}</span>
                            <h2 class="fw-bold text-primary my-1">${{ number_format($precio_total, 2) }}</h2>
                            <span
                                class="badge bg-secondary-subtle text-secondary border text-uppercase">{{ $globalSettings['moneda_sistema'] }}</span>
                        </div>

                        <div class="alert alert-light border small py-2 mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Ciclo actual:</span>
                                <span class="fw-bold text-dark">{{ $periodicidad_pagos }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Próximo Cobro:</span>
                                <span
                                    class="fw-bold text-dark">{{ $fecha_inicio_pagos ? date('d/m/Y', strtotime($fecha_inicio_pagos)) : '--/--/----' }}</span>
                            </div>
                        </div>

                        <ul class="list-group list-group-flush mb-4" style="font-size: 0.9rem;">
                            <li
                                class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                <span class="text-muted">Monto Base Paquete:</span>
                                <span class="fw-bold text-dark">${{ number_format($precio_paquete, 2) }}</span>
                            </li>
                            <li
                                class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                <div>
                                    <span class="text-muted d-block">Cargos Extra / Custom:</span>
                                </div>
                                <span class="fw-bold text-danger">+
                                    ${{ number_format($precio_extra, 2) }}</span>
                            </li>
                        </ul>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                            <span wire:loading wire:target="submit"
                                class="spinner-border spinner-border-sm me-2"></span>
                            <i class="bi bi-cloud-arrow-up-fill me-1" wire:loading.remove wire:target="submit"></i>
                            Guardar Contrato de Suscripción
                        </button>

                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
