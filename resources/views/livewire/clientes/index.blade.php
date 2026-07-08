@section('title', 'Clientes')

<div wire:init="init">
    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row justify-content-between">
        <div class="col-lg-auto mb-3">
            <div class="input-group">
                <span class="input-group-text"><x-icon name="search" /></span>
                <input type="search" placeholder="Buscar Clientes" class="form-control"
                    wire:model.debounce.500ms="search">
            </div>
        </div>
        <div class="col-lg-auto mb-3">
            @can('createCliente', [App\Models\Cliente::class])
                <button type="button" class="btn btn-site-primary btn-outline-warning"
                    wire:click="$emit('openModal', 'clientes.save')">
                    <x-icon name="plus-lg" />
                    Crear
                </button>
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

            <x-dropdown icon="filter" :label="__($filter)">
                @foreach ($filters as $filter)
                    @if ($this->filter == $filter)
                        <x-dropdown-item :label="__($filter)" class="active"
                            click="$set('filter', '{{ $filter }}')" />
                    @else
                        <x-dropdown-item :label="__($filter)" click="$set('filter', '{{ $filter }}')" />
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
                @forelse($clientes as $cliente)
                    <tr>
                        <td>{{ $cliente['nombre_comercial'] }}</td>
                        <td>{{ $cliente['rfc'] }}</td>
                        <td>{{ $cliente['razon_social'] }}</td>
                        <td>{{ $cliente['telefono'] }}</td>
                        <td class="text-center">
                            <ul class="list-unstyled mb-0">
                                @if (!$cliente['deleted_at'])
                                    @can('updateCliente', App\Models\Cliente::find($cliente['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="pencil" title="Modificar"
                                                click="$emit('openModal', 'clientes.save', {cliente: {{ $cliente['id'] }}})" />
                                        </li>
                                    @endcan
                                    @can('manageClientSuscripcion', App\Models\Cliente::find($cliente['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="bag-check" title="Nueva Suscripción"
                                                click="gestionarSuscripcion({{ $cliente['id'] }})" />
                                        </li>
                                    @endcan
                                    @can('deleteCliente', App\Models\Cliente::find($cliente['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="trash" title="Desactivar"
                                                click="$emit('openModal', 'clientes.delete', {cliente: {{ $cliente['id'] }}})" />
                                        </li>
                                    @endcan
                                @else
                                    @can('restoreCliente', App\Models\Cliente::withTrashed()->find($cliente['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="check-circle" title="Reactivar"
                                                click="$emit('openModal', 'clientes.restore', {cliente_id: {{ $cliente['id'] }}})" />
                                        </li>
                                    @endcan
                                @endif
                            </ul>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="list-group-item">
                                No se encontraron resultados...
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination :links="$clientes" :count="true" />
</div>
