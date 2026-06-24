@section('title', 'Terminales')

<div wire:init="init">
    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row justify-content-between">
        <div class="col-lg-auto mb-3">
            <div class="input-group">
                <span class="input-group-text"><x-icon name="search" /></span>
                <input type="search" placeholder="Buscar Terminales" class="form-control"
                    wire:model.debounce.500ms="search">
            </div>
        </div>
        <div class="col-lg-auto mb-3">
            @can('create', [App\Models\Terminal::class])
                <button type="button" class="btn btn-site-primary btn-outline-warning"
                    wire:click="$emit('openModal', 'terminales.save')">
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
                @forelse($terminales as $terminal)
                    <tr>
                        <td>{{ $terminal['identificador'] }}</td>
                        <td>{{ $terminal['sucursal'] }}</td>
                        <td>{{ $terminal['comentarios'] }}</td>
                        <td class="text-center">
                            <ul class="list-unstyled mb-0">
                                @if (!$terminal['deleted_at'])
                                    @can('update', App\Models\Terminal::find($terminal['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="pencil" title="Modificar"
                                                click="$emit('openModal', 'terminales.save', {terminal: {{ $terminal['id'] }}})" />
                                        </li>
                                    @endcan
                                    @can('delete', App\Models\Terminal::find($terminal['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="trash" title="Desactivar"
                                                click="$emit('openModal', 'terminales.delete', {terminal: {{ $terminal['id'] }}})" />
                                        </li>
                                    @endcan
                                @else
                                    @can('restore', App\Models\Terminal::withTrashed()->find($terminal['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="check-circle" title="Reactivar"
                                                click="$emit('openModal', 'terminales.restore', {terminal: {{ $terminal['id'] }}})" />
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

    <x-pagination :links="$terminales" :count="true" />
</div>
