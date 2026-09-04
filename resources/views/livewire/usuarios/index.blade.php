@section('title', __('site.users.list.users'))

<div wire:init="init">
    <h1 class="fs-1 mb-2 text-capitalize">@yield('title')</h1>

    <div class="row justify-content-between">
        <div class="col-md-9 col-12 mb-3 row">
            @if (user()->hasAnyRole(['SuperAdmin', 'Accountant']))
                <div class="col-6">
                    <x-select2-multiple label="{{ __('site.users.list.client') }}" model="clientes" class="form-control"
                        :lazy="true" :options="$clientesAll"></x-select2-multiple>
                </div>
            @endif
            <div class="col-6">
                <div class="input-group pt-4">
                    <span class="input-group-text"><x-icon name="search" /></span>
                    <input type="search" placeholder="{{ __('site.users.list.search_users') }}" class="form-control"
                        wire:model.live.debounce.500ms="search">
                </div>
            </div>
        </div>
        <div class="col-md-3 col-12 mb-3 text-end">
            @can('create', [App\Models\User::class])
                @if (user()->hasAnyRole(['Admin', 'Manager']))
                    <button type="button" class="btn btn-site-primary btn-outline-warning"
                        wire:click="$dispatch('openModal', { component: 'usuarios.save' })">
                        <x-icon name="plus-lg" />
                        {{ __('site.common.create') }}
                    </button>
                @else
                    <button type="button" class="btn btn-site-primary btn-outline-warning"
                        wire:click="$dispatch('openModal', { component: 'usuarios.save-system' })">
                        <x-icon name="plus-lg" />
                        {{ __('site.common.create') }}
                    </button>
                @endif
            @endcan

            <x-dropdown icon="eye" :label="__($perPage)">
                @foreach ($perPages as $perPage)
                    <x-dropdown-item label="{{ $perPage }}" click="$set('perPage', {{ $perPage }})" />
                @endforeach
            </x-dropdown>

            <x-dropdown icon="filter" :label="__($filter)">
                @foreach ($filters as $filter)
                    <x-dropdown-item :label="$filter" click="$set('filter', '{{ $filter }}')" />
                @endforeach
            </x-dropdown>
        </div>
    </div>

    <div class="list-group mb-3 table-responsive">
        <table class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th class="text-center text-uppercase">{{ __('site.users.list.picture') }}</th>
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
                    <th class="text-center uppercase" style="width: 100px">{{ __('site.common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usuario)
                    <tr>
                        <td class="text-center">
                            <img src="{{ $usuario['avatar'] ? asset("avatars/{$usuario['avatar']}") : asset('img/avatars/no_avatar.png') }}"
                                alt="Foto Usuario" style="width: 80px" class="img-thumbnail m-auto" loading="lazy" decoding="async">
                        </td>
                        <td>{{ $usuario['nombre'] }}</td>
                        <td>{{ $usuario['email'] }}</td>
                        <td class="text-uppercase">
                            {{ Illuminate\Support\Str::replaceLast(', ', ' ' . __('site.common.and') . ' ', __('site.roles.values.' . $usuario['roles'])) }}
                        </td>
                        @if (user()->hasRole('SuperAdmin'))
                            <td>{{ $usuario['cliente'] }}</td>
                        @endif
                        <td>{{ $usuario['suscripciones'] }}</td>
                        <td class="text-center">
                            <ul class="list-unstyled mb-0">
                                @if (!$usuario['deleted_at'])
                                    @can('update', App\Models\User::find($usuario['id']))
                                        @if (user()->hasAnyRole(['Admin', 'Manager']))
                                            <li class="list-inline-item">
                                                <x-action icon="pencil" title="{{ __('site.common.edit') }}"
                                                    click="$dispatch('openModal', { component: 'usuarios.save', arguments: {user: {{ $usuario['id'] }}} })" />
                                            </li>
                                        @else
                                            <li class="list-inline-item">
                                                <x-action icon="pencil" title="{{ __('site.common.edit') }}"
                                                    click="$dispatch('openModal', { component: 'usuarios.save-system', arguments: {user: {{ $usuario['id'] }}} })" />
                                            </li>
                                        @endif
                                    @endcan
                                    @can('setBranches', App\Models\User::find($usuario['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="building" title="{{ __('site.users.list.set_branches') }}"
                                                click="$dispatch('openModal', { component: 'usuarios.set-branches', arguments: {usuario: {{ $usuario['id'] }}} })" />
                                        </li>
                                    @endif
                                    @can('users-assignPermissions')
                                        @if (user()->hasRole('SuperAdmin'))
                                            <li class="list-inline-item">
                                                <x-action icon="gear"
                                                    title="{{ __('site.users.list.assign_permissions') }}"
                                                    click="$dispatch('openModal', { component: 'usuarios.manage-permissions-system', arguments: {usuario: {{ $usuario['id'] }}} })" />
                                            </li>
                                        @elseif(user()->hasRole('Admin') && !App\Models\User::find($usuario['id'])->hasRole('Admin'))
                                            <li class="list-inline-item">
                                                <x-action icon="gear"
                                                    title="{{ __('site.users.list.assign_permissions') }}"
                                                    click="$dispatch('openModal', { component: 'usuarios.manage-permissions', arguments: {usuario: {{ $usuario['id'] }}} })" />
                                            </li>
                                        @endif
                                    @endcan
                                    @can('delete', App\Models\User::find($usuario['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="trash" title="{{ __('site.common.deactivate') }}"
                                                click="$dispatch('openModal', { component: 'usuarios.delete', arguments: {usuario: {{ $usuario['id'] }}} })" />
                                        </li>
                                    @endcan
                                @else
                                    @can('restore', App\Models\User::withTrashed()->find($usuario['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="check-circle" title="{{ __('site.common.restore') }}"
                                                click="$dispatch('openModal', { component: 'usuarios.restore', arguments: {usuario: {{ $usuario['id'] }}} })" />
                                        </li>
                                    @endcan
                                @endif
                            </ul>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ user()->hasAnyRole(['Admin', 'Manager']) ? 6 : 7 }}">
                            <div class="list-group-item">
                                {{ __('site.common.results_not_found') }}
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination :links="$usuarios" :count="true" justify="between" class="mt-4" />
</div>
