@section('title', __('site.subscriptions.manage_subscription.title'))

<div class="container-fluid py-3">
    <style>
        /* Pequeños tweaks CSS complementarios para clavar la estética de la imagen */
        .fw-black {
            font-weight: 900;
        }

        .fs-7 {
            font-size: 0.85rem !important;
        }

        .fs-8 {
            font-size: 0.72rem !important;
        }

        .tracking-wider {
            letter-spacing: 1px;
        }

        .tracking-wide {
            letter-spacing: 0.5px;
        }

        .transition-all {
            transition: all 0.2s ease-in-out;
        }

        .transition-all:hover {
            transform: translateY(-2px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08) !important;
        }

        .border-dashed {
            border-style: dashed !important;
        }

        .check-modulo-custom {
            width: 2.2em !important;
            height: 1.1em !important;
            cursor: pointer;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .fs-9 {
            font-size: 0.65rem !important;
        }
    </style>
    <div class="card shadow-sm border-0 mb-4 bg-dark text-white rounded-3 overflow-hidden">
        <div class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <span
                    class="badge bg-primary-subtle text-primary text-uppercase px-2 py-1 mb-2 tracking-wider small fw-bold">
                    {{ __('site.subscriptions.manage_subscription.contractual_allocation_resources') }}
                </span>
                <h2 class="mb-1 fw-black text-white h3 d-flex align-items-center gap-2">
                    {{ $this->cliente ? Illuminate\Support\Facades\Crypt::decrypt($this->cliente->nombre_comercial) : __('site.subscriptions.manage_subscription.select_client') }}
                    @if ($this->cliente && $this->cliente->es_cliente_fiel)
                        <span class="badge bg-warning text-dark px-2 py-1 fs-8 tracking-wide shadow-sm"
                            title="Cliente con historial de fidelidad">
                            <i class="bi bi-star-fill me-1"></i>
                            {{ __('site.subscriptions.manage_subscription.loyal_client') }}
                        </span>
                    @endif
                </h2>
                @if ($this->cliente)
                    <p class="mb-0 text-white-50 small">
                        <i class="bi bi-building me-1"></i>
                        {{ __('site.subscriptions.manage_subscription.client_identifier') }} <span
                            class="text-info fw-bold">#{{ $this->cliente->id }}</span>
                    </p>
                @endif
            </div>
            <div
                class="text-md-end d-flex flex-row flex-md-column gap-2 justify-content-start align-items-center align-items-md-end">
                @php
                    $classEstado = 'dark';
                    $estado_str = __('site.subscriptions.manage_subscription.no_status');
                    switch ($estado) {
                        case 'PENDIENTE':
                            $classEstado = 'primary';
                            $estado_str = __('site.subscriptions.manage_subscription.pending');
                            break;
                        case 'ACTIVA':
                            $classEstado = 'success';
                            $estado_str = __('site.subscriptions.manage_subscription.active');
                            break;
                        case 'VENCIDA':
                            $classEstado = 'warning';
                            $estado_str = __('site.subscriptions.manage_subscription.expired');
                            break;
                        case 'INACTIVA':
                            $classEstado = 'danger';
                            $estado_str = __('site.subscriptions.manage_subscription.inactive');
                            break;
                    }
                @endphp
                <span class="badge bg-{{ $classEstado }} px-3 py-1.5 fs-6 shadow-sm">{{ $estado_str }}</span>
                <span class="badge bg-white text-dark border fs-7 py-1.5 shadow-sm">
                    <i class="bi bi-box-seam me-1 text-primary"></i>
                    {{ $paquete_id ? __('site.subscriptions.manage_subscription.subscription_by_package') : __('site.subscriptions.manage_subscription.custom_subscription') }}
                </span>

                @if ($suscripcion && $suscripcion->id)
                    <div class="d-flex gap-2 mt-1">
                        @if ($estado !== 'ACTIVA')
                            <button type="button" wire:loading.attr="disabled"
                                wire:click="$emit('openModal', 'suscripciones.activar', {'scope': 'suscripciones.gestion-suscripciones', 'suscripcion': {{ $suscripcion->id }}})"
                                class="btn btn-success btn-sm fw-bold shadow-sm">
                                <i class="bi bi-check-circle-fill me-1"
                                    wire:target="activarSuscripcion"></i>
                                Activar
                            </button>
                        @endif

                        {{-- @if ($estado === 'ACTIVA')
                            <button type="button" wire:loading.attr="disabled" wire:target="desactivarSuscripcion"
                                onclick="if(confirm('¿Confirmas desactivar esta suscripción? El cliente perderá el acceso al sistema.')) { @this.call('desactivarSuscripcion') }"
                                class="btn btn-danger btn-sm fw-bold shadow-sm">
                                <span wire:loading wire:target="desactivarSuscripcion"
                                    class="spinner-border spinner-border-sm me-1" role="status"></span>
                                <i class="bi bi-x-circle-fill me-1" wire:loading.remove
                                    wire:target="desactivarSuscripcion"></i>
                                Desactivar
                            </button>
                        @endif --}}
                    </div>
                @endif
            </div>
        </div>

        @if ($this->cliente && $this->cliente->es_cliente_fiel)
            <div class="bg-warning bg-opacity-10 border-top border-warning px-4 py-2 d-flex align-items-center gap-2">
                <i class="bi bi-award-fill text-warning fs-5"></i>
                <span class="text-warning fs-7 fw-bold">
                    {{ __('site.subscriptions.manage_subscription.loyal_client_message') }}
                </span>
            </div>
        @endif
    </div>

    <form wire:submit.prevent="submit">
        <div class="row g-4">

            <div class="col-lg-7">

                <div class="card shadow-sm border-0 mb-4 rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                            <div class="bg-primary-subtle text-primary rounded p-2 me-3"><i
                                    class="bi bi-sliders fs-5"></i></div>
                            <h5 class="mb-0 text-dark fw-bold">
                                {{ __('site.subscriptions.manage_subscription.subscription_configs') }}</h5>
                        </div>

                        @if (!$suscripcion || !$suscripcion->id)
                            <div class="mb-4 p-3 bg-light rounded-3 border">
                                <label
                                    class="form-label fw-bold text-secondary small">{{ __('site.subscriptions.manage_subscription.select_client') }}:</label>
                                <x-select2 :dynamic="true" :lazy="true" model="cliente_id" :options="$clientes"
                                    class="form-control" />
                                <div class="text-muted fs-7 mt-2">
                                    <i class="bi bi-info-circle-fill text-warning me-1"></i>
                                    {{ __('site.subscriptions.manage_subscription.select_client_message') }}
                                </div>
                            </div>
                        @endif

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary small mb-3">
                                <i class="bi bi-box-seam me-1"></i>
                                {{ __('site.subscriptions.manage_subscription.select_package') }}:
                            </label>

                            <div class="row g-3 row-cols-1 row-cols-md-2 row-cols-xl-3">

                                <div class="col">
                                    <div wire:click="$set('paquete_id', '')"
                                        class="card h-100 border transition-all rounded-3 position-relative cursor-pointer {{ $paquete_id == '' ? 'border-primary shadow bg-primary-subtle bg-opacity-10' : 'border-secondary-subtle bg-white' }}">
                                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <span
                                                        class="badge {{ $paquete_id == '' ? 'bg-primary' : 'bg-secondary' }} text-uppercase tracking-wider fs-8 px-2 py-1">{{ __('site.subscriptions.manage_subscription.custom') }}</span>
                                                    @if ($paquete_id == '')
                                                        <i class="bi bi-check-circle-fill text-primary fs-5"></i>
                                                    @endif
                                                </div>
                                                <h6 class="fw-bold text-dark mb-2">
                                                    {{ __('site.subscriptions.manage_subscription.tailor_made_plan') }}
                                                </h6>
                                                <p class="text-muted fs-7 mb-3">
                                                    {{ __('site.subscriptions.manage_subscription.tailor_made_plan_message') }}
                                                </p>
                                            </div>
                                            <div class="border-top pt-2 mt-2 text-center">
                                                <span
                                                    class="fs-7 text-muted">{{ __('site.subscriptions.manage_subscription.base_price') }}:</span>
                                                <span class="fw-black text-dark d-block fs-5">$0.00 <small
                                                        class="fs-8 text-uppercase">{{ $globalSettings['moneda_sistema'] }}</small></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @foreach ($paquetes as $pkg)
                                    <div class="col">
                                        <div wire:click="$set('paquete_id', {{ $pkg->id }})"
                                            class="card h-100 border transition-all rounded-3 position-relative cursor-pointer {{ $paquete_id == $pkg->id ? 'border-primary shadow bg-primary-subtle bg-opacity-10' : 'border-secondary-subtle bg-white' }}">

                                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                                <div>
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <span
                                                            class="badge {{ $paquete_id == $pkg->id ? 'bg-primary' : 'bg-dark-subtle text-dark' }} text-uppercase tracking-wider fs-8 px-2 py-1">
                                                            {{ __('site.subscriptions.manage_subscription.package') }}
                                                        </span>
                                                        @if ($paquete_id == $pkg->id)
                                                            <i class="bi bi-check-circle-fill text-primary fs-5"></i>
                                                        @endif
                                                    </div>

                                                    <h6 class="fw-bold text-dark mb-1">{{ $pkg->nombre }}</h6>

                                                    <p class="text-muted fs-7 mb-2 text-truncate-2">
                                                        {{ $pkg->descripcion ?? __('site.subscriptions.manage_subscription.no_description_message') }}
                                                    </p>

                                                    <div class="bg-light rounded-2 p-2 mb-2 fs-8 text-secondary">
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span><i class="bi bi-building me-1"></i>
                                                                {{ __('site.subscriptions.manage_subscription.base_branches') }}:
                                                            </span>
                                                            <span
                                                                class="fw-bold text-dark">{{ $pkg->cant_sucursales }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span><i class="bi bi-display me-1"></i>
                                                                {{ __('site.subscriptions.manage_subscription.base_terminals') }}:
                                                            </span>
                                                            <span
                                                                class="fw-bold text-dark">{{ $pkg->cant_terminales }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between">
                                                            <span><i class="bi bi-person me-1"></i>
                                                                {{ __('site.subscriptions.manage_subscription.base_users') }}:
                                                            </span>
                                                            <span
                                                                class="fw-bold text-dark">{{ $pkg->cant_usuarios }}</span>
                                                        </div>
                                                    </div>

                                                    @if (isset($pkg->modulos) && count($pkg->modulos) > 0)
                                                        <div class="mb-2">
                                                            <span
                                                                class="text-uppercase text-muted fw-bold tracking-wide fs-9 d-block mb-1">
                                                                {{ __('site.subscriptions.manage_subscription.included_modules') }}:
                                                            </span>
                                                            <div class="d-flex flex-wrap gap-1">
                                                                @foreach ($pkg->modulos as $modPkg)
                                                                    <span
                                                                        class="badge rounded-3 border bg-white text-dark p-1 fs-8 d-inline-flex align-items-center"
                                                                        title="{{ $modPkg->nombre }}">
                                                                        <i class="bi {{ $modPkg->icono }} me-1"
                                                                            style="color: {{ $modPkg->icono_color }}"></i>
                                                                        <span class="text-truncate"
                                                                            style="max-width: 70px;">{{ $modPkg->nombre }}</span>
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="border-top pt-2 mt-2 text-center">
                                                    <span
                                                        class="fs-7 text-muted">{{ __('site.subscriptions.manage_subscription.base_monthly_prices') }}:</span>
                                                    <span class="fw-black text-primary d-block fs-5">
                                                        ${{ number_format($pkg->precio, 2) }}
                                                        <small
                                                            class="fs-8 text-uppercase text-muted">{{ $globalSettings['moneda_sistema'] }}</small>
                                                    </span>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-uppercase text-muted fw-bold mb-3 tracking-wide fs-7">
                                <i class="bi bi-calendar3 me-1 text-primary"></i>
                                {{ __('site.subscriptions.manage_subscription.validity_and_nilling_cycle') }}
                            </h6>
                            <div class="row g-3 p-3 bg-light rounded-3 border mx-0">
                                <div class="col-md-6">
                                    <label
                                        class="form-label fw-bold text-dark small">{{ __('site.subscriptions.manage_subscription.operations_start') }}
                                        *</label>
                                    <input type="date"
                                        class="form-control border-secondary-subtle @error('fecha_inicio_operaciones') is-invalid @enderror"
                                        wire:model="fecha_inicio_operaciones">
                                    @error('fecha_inicio_operaciones')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label
                                        class="form-label fw-bold text-dark small">{{ __('site.subscriptions.manage_subscription.next_charge_date') }}
                                        *</label>
                                    <input type="date"
                                        class="form-control border-secondary-subtle @error('fecha_inicio_pagos') is-invalid @enderror"
                                        wire:model="fecha_inicio_pagos">
                                    @error('fecha_inicio_pagos')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 mt-3">
                                    <label
                                        class="form-label fw-bold text-dark small">{{ __('site.subscriptions.manage_subscription.payment_periodicity') }}
                                        *</label>
                                    <select
                                        class="form-select border-secondary-subtle @error('periodicidad_pagos') is-invalid @enderror"
                                        wire:model="periodicidad_pagos">
                                        <option value="MENSUAL">
                                            {{ __('site.subscriptions.manage_subscription.monthly') }}</option>
                                        <option value="BIMESTRAL">
                                            {{ __('site.subscriptions.manage_subscription.bimonthly') }}</option>
                                        <option value="TRIMESTRAL">
                                            {{ __('site.subscriptions.manage_subscription.quarterly') }}</option>
                                        <option value="SEMESTRAL">
                                            {{ __('site.subscriptions.manage_subscription.biannual') }}</option>
                                        <option value="ANUAL">
                                            {{ __('site.subscriptions.manage_subscription.annual') }}</option>
                                    </select>
                                    @error('periodicidad_pagos')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div>
                            <h6 class="text-uppercase text-muted fw-bold mb-3 tracking-wide fs-7">
                                <i class="bi bi-cpu-fill me-1 text-primary"></i>
                                {{ __('site.subscriptions.manage_subscription.capacity_infrastructure') }}
                            </h6>
                            <div class="row g-3 p-3 bg-light rounded-3 border mx-0">
                                @foreach ([['label' => 'Sucursales', 'model' => 'cant_sucursales', 'icon' => 'bi-building', 'inc' => 'incrementSucursales', 'dec' => 'decrementSucursales', 'res' => 'resetSucursales'], ['label' => 'Terminales (APOS)', 'model' => 'cant_terminales', 'icon' => 'bi-display', 'inc' => 'incrementTerminales', 'dec' => 'decrementTerminales', 'res' => 'resetTerminales'], ['label' => 'Usuarios Cloud', 'model' => 'cant_usuarios', 'icon' => 'bi-people', 'inc' => 'incrementUsuarios', 'dec' => 'decrementUsuarios', 'res' => 'resetUsuarios']] as $infra)
                                    <div class="col-md-4">
                                        <label
                                            class="form-label fw-bold text-dark small">{{ $infra['label'] }}</label>
                                        <div class="input-group input-group shadow-sm rounded">
                                            <span
                                                class="input-group-text bg-white border-secondary-subtle text-muted"><i
                                                    class="{{ $infra['icon'] }}"></i></span>
                                            <input type="number"
                                                class="form-control text-center bg-white border-secondary-subtle fw-bold @error($infra['model']) is-invalid @enderror"
                                                @if ($paquete_id) disabled @endif
                                                wire:model="{{ $infra['model'] }}" min="1">
                                            @if ($paquete_id)
                                                <button type="button" wire:click="{{ $infra['inc'] }}"
                                                    class="btn btn-success border-success text-white px-2"><i
                                                        class="bi bi-plus"></i></button>
                                                <button type="button" wire:click="{{ $infra['res'] }}"
                                                    class="btn btn-light border-secondary-subtle text-dark px-1.5"><i
                                                        class="bi bi-arrow-clockwise"></i></button>
                                                <button type="button" wire:click="{{ $infra['dec'] }}"
                                                    class="btn btn-danger border-danger text-white px-2"><i
                                                        class="bi bi-dash"></i></button>
                                            @endif
                                        </div>
                                        @error($infra['model'])
                                            <div class="invalid-feedback d-block fs-7">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-4">
                            <h6 class="text-uppercase text-muted fw-bold mb-3 tracking-wide fs-7">
                                <i class="bi bi-diagram-3-fill me-1 text-primary"></i> Recursos Vinculados a la
                                Suscripción
                            </h6>
                            <div class="row g-3 p-3 bg-light rounded-3 border mx-0">

                                {{-- SUCURSALES --}}
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-bold text-dark small mb-0">
                                            <i class="bi bi-building me-1"></i> Sucursales
                                            <span
                                                class="badge bg-{{ count($sucursales) == $cant_sucursales ? 'success' : (count($sucursales) > $cant_sucursales ? 'danger' : 'secondary') }} ms-1">
                                                {{ count($sucursales) }}/{{ $cant_sucursales }}
                                            </span>
                                        </label>
                                        @if (count($sucursales) < $cant_sucursales && $this->cliente_id)
                                            <button type="button" class="btn btn-sm btn-outline-success py-0 px-1"
                                                wire:click="$emit('openModal', 'sucursales.save', { scope: 'suscripciones.gestion-suscripciones', cliente_id: {{ $this->cliente_id }}, from_subscription: true })">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        @endif
                                    </div>
                                    <x-select2-multiple :dynamic="true" :lazy="true" model="sucursales"
                                        :options="$sucursalesDisponibles" class="form-control" :max-selections="$cant_sucursales" />
                                </div>

                                {{-- TERMINALES --}}
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-bold text-dark small mb-0">
                                            <i class="bi bi-display me-1"></i> Terminales
                                            <span
                                                class="badge bg-{{ count($terminales) == $cant_terminales ? 'success' : (count($terminales) > $cant_terminales ? 'danger' : 'secondary') }} ms-1">
                                                {{ count($terminales) }}/{{ $cant_terminales }}
                                            </span>
                                        </label>
                                        @if (count($terminales) < $cant_terminales && count($sucursales) == 1)
                                            <button type="button" class="btn btn-sm btn-outline-success py-0 px-1"
                                                wire:click="$emit('openModal', 'terminales.save-system', { scope: 'suscripciones.gestion-suscripciones', sucursal_id: {{ $sucursales[0] }}, from_subscription: true })">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        @endif
                                    </div>
                                    <x-select2-multiple :dynamic="true" :lazy="true" model="terminales"
                                        :options="$terminalesDisponibles" class="form-control" :max-selections="$cant_terminales" />
                                </div>

                                {{-- USUARIOS --}}
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-bold text-dark small mb-0">
                                            <i class="bi bi-people me-1"></i> Usuarios
                                            <span
                                                class="badge bg-{{ count($usuarios) == $cant_usuarios ? 'success' : (count($usuarios) > $cant_usuarios ? 'danger' : 'secondary') }} ms-1">
                                                {{ count($usuarios) }}/{{ $cant_usuarios }}
                                            </span>
                                        </label>
                                        @if (count($usuarios) < $cant_usuarios)
                                            <button type="button" class="btn btn-sm btn-outline-success py-0 px-1"
                                                wire:click="$dispatch('abrirModalCreacion', { tipo: 'usuario', cliente_id: {{ $this->cliente->id ?? 'null' }} })">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        @endif
                                    </div>
                                    <x-select2-multiple :dynamic="true" :lazy="true" model="usuarios"
                                        :options="$usuariosDisponibles" class="form-control" :max-selections="$cant_usuarios" />
                                </div>

                            </div>
                            <div class="fs-7 text-muted mt-2">
                                <i class="bi bi-info-circle-fill text-warning me-1"></i>
                                La cantidad de recursos vinculados no puede exceder la capacidad contratada. Al alcanzar
                                el límite, el selector bloqueará nuevas selecciones automáticamente.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 rounded-3">
                    <div
                        class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary-subtle text-primary rounded p-2 me-3"><i
                                    class="bi bi-grid-3x3-gap-fill fs-5"></i></div>
                            <h5 class="mb-0 text-dark fw-bold">Aprovisionamiento de Módulos</h5>
                        </div>
                        <span class="badge bg-primary px-3 py-1.5 rounded-pill fw-bold">{{ count($modulos) }} Módulos
                            Activos</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3" style="max-height: 400px; overflow-y: auto; padding-right: 4px;">
                            @foreach ($modulosDisponibles as $module)
                                <div class="col-md-6 col-12">
                                    <label
                                        class="card h-100 transition-all border p-3 rounded-3 position-relative {{ in_array($module->id, $modulos) ? 'border-primary shadow-sm bg-primary-subtle bg-opacity-10' : 'border-secondary-subtle bg-white' }}"
                                        style="cursor: pointer; min-height: 82px;">
                                        <div class="d-flex align-items-start">
                                            <div class="form-check form-switch me-2 pt-1">
                                                <input class="form-check-input check-modulo-custom" type="checkbox"
                                                    role="switch" value="{{ $module->id }}" wire:model="modulos"
                                                    @if ($paquete_id && in_array($module->id, $this->modulos_paquete)) disabled @endif
                                                    id="mod-{{ $module->id }}">
                                            </div>

                                            <div class="rounded-3 p-2 d-flex align-items-center justify-content-center me-3 shadow-sm flex-shrink-0"
                                                style="background-color: {{ $module->icono_color }}; width: 42px; height: 42px;">
                                                <i class="bi {{ $module->icono }} text-white fs-5"></i>
                                            </div>

                                            <div class="flex-grow-1 text-truncate" style="line-height: 1.2;">
                                                <h6 class="mb-1 fw-bold text-dark text-truncate small"
                                                    title="{{ $module->nombre }}">{{ $module->nombre }}</h6>
                                                <span class="text-muted fw-medium fs-7 d-block">
                                                    +${{ number_format($module->costo_base, 2) }} <span
                                                        class="fs-8 text-uppercase opacity-70">{{ $globalSettings['moneda_sistema'] }}</span>
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm border-0 rounded-3 sticky-top"
                    style="top: 24px; border-top: 4px solid var(--bs-primary) !important;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 text-uppercase fw-black text-secondary tracking-wide fs-7">
                            <i class="bi bi-receipt me-2 text-primary"></i> Estructura de Precios Mensual
                        </h6>
                    </div>
                    <div class="card-body p-4">

                        <div class="text-center mb-4 bg-light border rounded-3 p-4">
                            <span class="text-uppercase text-muted fw-bold tracking-wider fs-8 d-block mb-1">Costo
                                Estimado Recurrente ({{ $periodicidad_pagos }})</span>
                            <h2 class="fw-black text-primary display-6 mb-1">${{ number_format($total, 2) }}
                            </h2>
                            <span
                                class="badge bg-dark-subtle text-dark px-3 py-1 text-uppercase font-monospace tracking-wide fs-8">{{ $globalSettings['moneda_sistema'] }}
                                / Neto</span>
                        </div>

                        <div class="p-3 bg-light rounded-3 border border-dashed mb-4 fs-7">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted"><i class="bi bi-arrow-repeat me-1"></i> Ciclo de
                                    facturación:</span>
                                <span
                                    class="fw-bold text-dark text-uppercase tracking-wide">{{ $periodicidad_pagos }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted"><i class="bi bi-calendar-event me-1"></i> Próximo
                                    cobro:</span>
                                <span
                                    class="fw-bold text-primary">{{ $fecha_inicio_pagos ? date('d/m/Y', strtotime($fecha_inicio_pagos)) : '— / — / —' }}</span>
                            </div>
                        </div>

                        <h6 class="text-uppercase text-muted fw-bold tracking-wider fs-8 mb-2">Detalle de Cargos</h6>
                        <div class="list-group list-group-flush border-bottom mb-3">
                            <div
                                class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5 bg-transparent fs-7">
                                <div class="text-dark"><i class="bi bi-box me-2 text-secondary"></i>Base Cloud (Plan
                                    Seleccionado)</div>
                                <span class="fw-bold text-dark">${{ number_format($precio_paquete, 2) }}</span>
                            </div>
                            <div
                                class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5 bg-transparent fs-7">
                                <div class="text-dark"><i class="bi bi-plus-circle me-2 text-danger"></i>Módulos Extra
                                    / Custom</div>
                                <span class="fw-bold text-danger">+ ${{ number_format($precio_extra, 2) }}</span>
                            </div>
                            @if ($descuento > 0)
                                <div
                                    class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5 bg-transparent fs-7">
                                    <div class="text-success">
                                        <i class="bi bi-dash-circle me-2"></i>Descuento Aplicado
                                        <span class="badge bg-success-subtle text-success fs-9 ms-1">
                                            -{{ number_format($this->porcentaje_descuento, 1) }}%
                                        </span>
                                    </div>
                                    <span class="fw-bold text-success">- ${{ number_format($descuento, 2) }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small">Descuento
                                <span class="text-muted fw-normal">(monto fijo, opcional)</span>
                            </label>
                            <div class="input-group shadow-sm rounded">
                                <span class="input-group-text bg-white border-secondary-subtle text-muted">
                                    <i class="bi bi-tag-fill text-success"></i>
                                </span>
                                <input type="number" step="0.01" min="0"
                                    class="form-control bg-white border-secondary-subtle fw-bold @error('descuento') is-invalid @enderror"
                                    wire:model.lazy="descuento" placeholder="0.00">
                                <span
                                    class="input-group-text bg-white border-secondary-subtle text-muted text-uppercase fs-8">{{ $globalSettings['moneda_sistema'] }}</span>
                            </div>
                            @if ($descuento > 0)
                                <div class="fs-8 text-success mt-1">
                                    <i class="bi bi-info-circle me-1"></i> Este descuento equivale al
                                    <strong>{{ number_format($this->porcentaje_descuento, 1) }}%</strong> del subtotal.
                                </div>
                            @endif
                            @error('descuento')
                                <div class="invalid-feedback d-block fs-7">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit"
                            class="btn btn-primary btn-lg w-100 py-3 fw-bold rounded-3 shadow-sm text-uppercase tracking-wide fs-7">
                            <span wire:loading wire:target="submit" class="spinner-border spinner-border-sm me-2"
                                role="status"></span>
                            <i class="bi bi-cloud-arrow-up-fill me-2" wire:loading.remove wire:target="submit"></i>
                            Guardar Contrato de Suscripción
                        </button>

                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
