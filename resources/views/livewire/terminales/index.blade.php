@section('title', __('site.terminals.index.title'))

<div wire:init="init">
    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row justify-content-between">
        <div class="col-md-9 col-12 mb-3 row">
            @if (user()->hasAnyRole(['SuperAdmin', 'Accountant']))
                <div class="col-md-3 col-12">
                    <x-select2-multiple class="form-control" label="{{ __('site.terminals.index.client') }}"
                        :lazy="true" model="clientes" :options="$clientesAll" />
                </div>
            @endif
            <div class="col-md-3 col-12">
                <x-select2-multiple class="form-control" label="{{ __('site.terminals.index.branch') }}"
                    :lazy="true" :dynamic="true" model="sucursales" :options="$sucursalesAll" />
            </div>
            <div class="col-md-3 col-12">
                <x-select2-multiple class="form-control" label="{{ __('site.terminals.index.subscription') }}"
                    :lazy="true" :dynamic="true" model="suscripciones" :options="$suscripcionesAll" />
            </div>
            <div class="col-md-3 col-12">
                <div class="input-group pt-4">
                    <span class="input-group-text"><x-icon name="search" /></span>
                    <input type="search" placeholder="{{ __('site.terminals.index.search_terminals') }}"
                        class="form-control" wire:model.live.debounce.500ms="search">
                </div>
            </div>
        </div>
        <div class="col-md-3 col-12 mb-3">
            @can('create', [App\Models\Terminal::class])
                @if (user()->hasAnyRole(['SuperAdmin', 'Accountant']))
                    <button type="button" class="btn btn-site-primary btn-outline-warning"
                        wire:click="$dispatch('openModal', { component: 'terminales.save-system' })">
                        <x-icon name="plus-lg" />
                        {{ __('site.common.create') }}
                    </button>
                @endif
            @endcan

            <x-dropdown icon="eye" :label="__($perPage)">
                @foreach ($perPages as $perPage)
                    @if ($perPage == $this->perPage)
                        <x-dropdown-item label="{{ $perPage }}" class="active"
                            click="$set('perPage', {{ $perPage }})" />
                    @else
                        <x-dropdown-item label="{{ $perPage }}" click="$set('perPage', '{{ $perPage }}')" />
                    @endif
                @endforeach
            </x-dropdown>

            <x-dropdown icon="filter" :label="$filter">
                @foreach ($filters as $filter)
                    @if ($this->filter == $filter)
                        <x-dropdown-item :label="$filter" class="active"
                            click="$set('filter', '{{ $filter }}')" />
                    @else
                        <x-dropdown-item :label="$filter" click="$set('filter', '{{ $filter }}')" />
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
                @forelse($terminales as $terminal)
                    <tr>
                        <td>{{ $terminal['identificador'] }}</td>
                        <td>{{ $terminal['nombre'] }}</td>
                        <td>
                            @if ($terminal['es_vk'])
                                <span
                                    class="badge bg-primary-subtle text-primary text-uppercase">{{ __('site.common.yes') }}</span>
                            @else
                                <span
                                    class="badge bg-danger-subtle text-danger text-uppercase">{{ __('site.common.no') }}</span>
                            @endif
                        </td>
                        <td>{{ $terminal['sucursal'] }}</td>
                        @if (user()->hasAnyRole(['SuperAdmin', 'Accountant']))
                            <td>{{ $terminal['cliente'] }}</td>
                        @endif
                        <td>{{ $terminal['suscripcion'] }}</td>
                        <td>{{ $terminal['comentarios'] }}</td>
                        <td class="text-center">
                            <ul class="list-unstyled mb-0">
                                @if (!$terminal['deleted_at'])
                                    @can('update', App\Models\Terminal::find($terminal['id']))
                                        @if (user()->hasAnyRole(['Admin', 'Manager']))
                                            <li class="list-inline-item">
                                                <x-action icon="pencil" title="{{ __('site.common.edit') }}"
                                                    click="$dispatch('openModal', { component: 'terminales.save', arguments: {terminal: {{ $terminal['id'] }}} })" />
                                            </li>
                                        @else
                                            <li class="list-inline-item">
                                                <x-action icon="pencil" title="{{ __('site.common.edit') }}"
                                                    click="$dispatch('openModal', { component: 'terminales.save-system', arguments: {terminal: {{ $terminal['id'] }}} })" />
                                            </li>
                                        @endif
                                    @endcan
                                    @can('delete', App\Models\Terminal::find($terminal['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="trash" title="{{ __('site.common.deactivate') }}"
                                                click="$dispatch('openModal', { component: 'terminales.delete', arguments: {terminal: {{ $terminal['id'] }}} })" />
                                        </li>
                                    @endcan
                                @else
                                    @can('restore', App\Models\Terminal::withTrashed()->find($terminal['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="check-circle" title="{{ __('site.common.restore') }}"
                                                click="$dispatch('openModal', { component: 'terminales.restore', arguments: {terminal: {{ $terminal['id'] }}} })" />
                                        </li>
                                    @endcan
                                @endif
                            </ul>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ user()->hasAnyRole(['Admin', 'Manager']) ? 7 : 8 }}">
                            <div class="list-group-item">
                                {{ __('site.common.results_not_found') }}
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination :links="$terminales" :count="true" :justify="'between'" class="mt-4" />
</div>
