@section('title', __('site.branches.index.title'))

<div wire:init="init">
    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row justify-content-between">
        <div class="col-md-9 col-12 mb-3 row">
            @if (user()->hasAnyRole(['SuperAdmin', 'Accountant']))
                <div class="col-6">
                    <x-select2-multiple label="{{ __('site.branches.index.client') }}" model="clientes"
                        class="form-control" :lazy="true" :options="$clientesAll"></x-select2-multiple>
                </div>
            @endif
            <div class="col-6">
                <div class="input-group pt-4">
                    <span class="input-group-text"><x-icon name="search" /></span>
                    <input type="search" placeholder="{{ __('site.branches.index.search_branches') }}"
                        class="form-control" wire:model.live.debounce.500ms="search">
                </div>
            </div>
        </div>
        <div class="col-md-3 col-12 mb-3 text-end">
            @can('create', [App\Models\Sucursal::class])
                <button type="button" class="btn btn-site-primary btn-outline-warning"
                    wire:click="$dispatch('openModal', { component: 'sucursales.save' })">
                    <x-icon name="plus-lg" />
                    {{ __('site.common.create') }}
                </button>
            @endcan

            <x-dropdown icon="eye" :label="__($perPage)">
                @foreach ($perPages as $perPage)
                    @if ($perPage == $this->perPage)
                        <x-dropdown-item label="{{ $perPage }}" class="active"
                            click="$set('perPage', {{ $perPage }})" href="#" />
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
                    <th class="text-center">{{ __('site.branches.index.logo') }}</th>
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
                @forelse($sucursales as $sucursal)
                    <tr>
                        <td>
                            <img src="{{ $sucursal['logo'] ? asset("logos/{$sucursal['logo']}") : asset('img/no_image.png') }}"
                                alt="Logo Sucursal" style="width: 80px" class="img-thumbnail m-auto">
                        </td>
                        <td>{{ $sucursal['nombre_comercial'] }}</td>
                        <td>{{ $sucursal['rfc'] }}</td>
                        <td>{{ $sucursal['razon_social'] }}</td>
                        <td>{{ $sucursal['telefono'] }}</td>
                        @if (user()->hasAnyRole(['SuperAdmin', 'Accountant']))
                            <td>{{ $sucursal['cliente'] }}</td>
                        @endif
                        <td>{{ $sucursal['suscripcion'] }}</td>
                        <td class="text-center">
                            <ul class="list-unstyled mb-0">
                                @if (!$sucursal['deleted_at'])
                                    @can('update', App\Models\Sucursal::find($sucursal['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="pencil" title="{{ __('site.common.edit') }}"
                                                click="$dispatch('openModal', { component: 'sucursales.save', arguments: {sucursal : {{ $sucursal['id'] }}} })" />
                                        </li>
                                    @endcan
                                    @can('setPaymentForms', App\Models\Sucursal::find($sucursal['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="card-list" title="{{ __('site.common.payment_form') }}"
                                                click="$dispatch('openModal', { component: 'sucursales.formas-pago', arguments: {sucursal: {{ $sucursal['id'] }}} })" />
                                        </li>
                                    @endcan
                                    @can('setConfigs', App\Models\Sucursal::find($sucursal['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="gear" title="{{ __('site.common.configs') }}"
                                                click="$dispatch('openModal', { component: 'sucursales.configuraciones', arguments: {sucursal: {{ $sucursal['id'] }}} })" />
                                        </li>
                                    @endcan
                                    @can('delete', App\Models\Sucursal::find($sucursal['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="trash" title="{{ __('site.common.deactivate') }}"
                                                click="$dispatch('openModal', { component: 'sucursales.delete', arguments: {sucursal: {{ $sucursal['id'] }}} })" />
                                        </li>
                                    @endcan
                                @else
                                    @can('restore', App\Models\Sucursal::withTrashed()->find($sucursal['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="check-circle" title="{{ __('site.common.restore') }}"
                                                click="$dispatch('openModal', { component: 'sucursales.restore', arguments: {sucursal_id: {{ $sucursal['id'] }}} })" />
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

    <x-pagination :links="$sucursales" :count="true" justify="between" class="mt-4" />
</div>
