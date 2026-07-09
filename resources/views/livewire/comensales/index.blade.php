@section('title', __('site.diners.index.title'))

<div wire:init="init">
    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row justify-content-between">
        <div class="col-lg-auto mb-3">
            <div class="input-group">
                <span class="input-group-text"><x-icon name="search" /></span>
                <input type="search" placeholder="{{ __('site.diners.index.search_diners') }}" class="form-control"
                    wire:model.debounce.500ms="search">
            </div>
        </div>
        <div class="col-lg-auto mb-3">
            @can('createComensal', [App\Models\Cliente::class])
                <button type="button" class="btn btn-site-primary btn-outline-warning"
                    wire:click="$emit('openModal', 'comensales.save')">
                    <x-icon name="plus-lg" />
                    {{ __('site.common.create') }}
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
                        <th class="text-left cursor-pointer text-uppercase" style="white-space: nowrap !important"
                            wire:click="changeSort('{{ $sort }}')">
                            <span>
                                @if ($this->sort == $sort)
                                    <i class="{{ $this->class_sort }}"></i>
                                @endif {{ $sort }}
                            </span>
                        </th>
                    @endforeach
                    <th class="text-center text-uppercase">{{ __('site.common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $cliente)
                    <tr>
                        <td>{{ $cliente['nombre_comercial'] }}</td>
                        <td>{{ $cliente['rfc'] }}</td>
                        <td>{{ $cliente['razon_social'] }}</td>
                        <td>{{ $cliente['telefono'] }}</td>
                        <td>
                            @if ($cliente['activo'])
                                <span
                                    class="badge bg-success-subtle text-success border-1 border-success">{{ __('site.common.active') }}</span>
                            @else
                                <span
                                    class="badge bg-danger-subtle text-danger border-1 border-danger">{{ __('site.common.inactive') }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <ul class="list-unstyled mb-0">
                                @if ($cliente['activo'])
                                    @can('updateComensal', App\Models\Cliente::find($cliente['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="pencil" title="{{ __('site.common.update') }}"
                                                click="$emit('openModal', 'comensales.save', {comensal: {{ $cliente['id'] }}})" />
                                        </li>
                                    @endcan
                                    @can('deleteComensal', App\Models\Cliente::find($cliente['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="trash" title="{{ __('site.common.deactivate') }}"
                                                click="$emit('openModal', 'comensales.delete', {comensal: {{ $cliente['id'] }}})" />
                                        </li>
                                    @endcan
                                @else
                                    @can('restoreComensal', App\Models\Cliente::withTrashed()->find($cliente['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="check-circle" title="{{ __('site.common.restore') }}"
                                                click="$emit('openModal', 'comensales.restore', {comensal_id: {{ $cliente['id'] }}})" />
                                        </li>
                                    @endcan
                                @endif
                            </ul>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="list-group-item">
                                {{ __('site.common.results_not_found') }}
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination :links="$clientes" :count="true" />
</div>
