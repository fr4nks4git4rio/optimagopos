@section('title', 'Suscripciones')

<div wire:init="init">
    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row justify-content-between">
        <div class="col-lg-auto mb-3">
            <div class="input-group">
                <span class="input-group-text"><x-icon name="search" /></span>
                <input type="search" placeholder="Buscar Suscripciones" class="form-control"
                    wire:model.debounce.500ms="search">
            </div>
        </div>
        <div class="col-lg-auto mb-3">
            @can('create', [App\Models\Suscripcion::class])
                <a href="{{ route('admin.clientes.suscripcion') }}" class="btn btn-site-primary btn-outline-warning">
                    <x-icon name="plus-lg" />
                    Crear
                </a>
            @endcan

            <x-dropdown icon="eye" :label="__($perPage)">
                @foreach ($perPages as $perPage)
                    @if ($perPage == $this->perPage)
                        <x-dropdown-item label="{{ $perPage }}" class="active"
                            click="$set('perPage', '{{ $perPage }}')" />
                    @else
                        <x-dropdown-item label="{{ $perPage }}" click="$set('perPage', '{{ $perPage }}')" />
                    @endif
                @endforeach
            </x-dropdown>
        </div>
    </div>

    <div class="list-group mb-3 table-responsive">
        <table class="table table-responsive table-striped">
            <thead>
                <tr>
                    @foreach ($sorts as $sort)
                        <th class="text-left cursor-pointer" style="white-space: nowrap !important"
                            wire:click="changeSort('{{ $sort }}')">
                            <span>
                                @if ($this->sort == $sort)
                                    <i class="{{ $this->class_sort }}"></i>
                                @endif {{ $sort }}
                            </span>
                        </th>
                    @endforeach
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suscripciones as $sub)
                    <tr>
                        <td>{{ $sub['cliente'] }}</td>
                        <td>{{ $sub['paquete'] ?? 'S/P' }}</td>
                        <td>{{ $sub['inicio_operaciones'] }}</td>
                        <td>{{ $sub['inicio_pagos'] }}</td>
                        <td>{{ $sub['periodicidad_pagos'] }}</td>
                        <td>
                            <ul class="list-unstyled">
                                @foreach (explode(', ', $sub['capacidad']) as $cap)
                                    <li>{{ $cap }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td>${{ number_format($sub['precio_total'], 2) }}</td>
                        <td class="text-center">
                            @php
                                $classEstado = 'dark';
                                switch ($sub['estado']) {
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
                                class="badge bg-{{ $classEstado }}-subtle text-{{ $classEstado }} border-1 border-{{ $classEstado }}">
                                {{ $sub['estado'] }}
                            </span>
                        </td>
                        <td class="text-center">
                            <ul class="list-unstyled mb-0">
                                @if (in_array($sub['estado'], ['PENDIENTE', 'ACTIVA']))
                                    @can('update', App\Models\Suscripcion::find($sub['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="pencil" title="Modificar" :href="route('admin.clientes.suscripcion', $sub['cliente_id'])" />
                                        </li>
                                    @endcan
                                    @can('activate', App\Models\Suscripcion::find($sub['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="check2-square" title="Activar"
                                                click="$emit('openModal', 'suscripciones.activar', {'suscripcion': {{ $sub['id'] }}})" />
                                        </li>
                                    @endcan
                                    @can('revoke', App\Models\Suscripcion::find($sub['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="trash" title="Revocar" />
                                        </li>
                                    @endcan
                                @endif
                            </ul>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="list-group-item">
                                No se encontraron resultados...
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination :links="$suscripciones" :count="true" />
</div>
