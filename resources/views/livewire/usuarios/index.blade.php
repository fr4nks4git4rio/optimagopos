@section('title', __('site.users.list.users'))

<div wire:init="init">
    <h1 class="fs-1 mb-2 text-capitalize">@yield('title')</h1>

    <div class="row justify-content-between">
        <div class="col-lg-auto mb-3">
            <div class="input-group">
                <span class="input-group-text"><x-icon name="search" /></span>
                <input type="search" placeholder="{{ __('site.users.list.search_users') }}"
                    class="form-control text-capitalize" wire:model.debounce.500ms="search">
            </div>
        </div>
        <div class="col-lg-auto mb-3">
            @can('create', [App\Models\User::class])
                @if (user()->cliente_id)
                    <button type="button" class="btn btn-site-primary btn-outline-warning"
                        wire:click="$emit('openModal', 'usuarios.save')">
                        <x-icon name="plus-lg" />
                        {{ __('site.common.create') }}
                    </button>
                @else
                    <button type="button" class="btn btn-site-primary btn-outline-warning"
                        wire:click="$emit('openModal', 'usuarios.save-system')">
                        <x-icon name="plus-lg" />
                        {{ __('site.common.create') }}
                    </button>
                @endif
            @endcan

            <x-dropdown icon="eye" :label="__($perPage)">
                @foreach ($perPages as $perPage)
                    <x-dropdown-item label="{{ $perPage }}" click="$set('perPage', '{{ $perPage }}')" />
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
                                alt="Foto Usuario" style="width: 80px" class="img-thumbnail m-auto">
                        </td>
                        <td>{{ $usuario['nombre'] }}</td>
                        <td>{{ $usuario['email'] }}</td>
                        @if (user()->is_super_admin)
                            <td>{{ $usuario['cliente'] }}</td>
                        @endif
                        <td class="text-center">
                            <ul class="list-unstyled mb-0">
                                @if (!$usuario['deleted_at'])
                                    @can('update', App\Models\User::find($usuario['id']))
                                        @if (user()->cliente_id)
                                            <li class="list-inline-item">
                                                <x-action icon="pencil" title="{{ __('site.common.edit') }}"
                                                    click="$emit('openModal', 'usuarios.save', {user: {{ $usuario['id'] }}})" />
                                            </li>
                                        @else
                                            <li class="list-inline-item">
                                                <x-action icon="pencil" title="{{ __('site.common.edit') }}"
                                                    click="$emit('openModal', 'usuarios.save-system', {user: {{ $usuario['id'] }}})" />
                                            </li>
                                        @endif
                                    @endcan
                                    @can('delete', App\Models\User::find($usuario['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="trash" title="{{ __('site.common.deactivate') }}"
                                                click="$emit('openModal', 'usuarios.delete', {usuario: {{ $usuario['id'] }}})" />
                                        </li>
                                    @endcan
                                @else
                                    @can('restore', App\Models\User::withTrashed()->find($usuario['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="check-circle" title="{{ __('site.common.restore') }}"
                                                click="$emit('openModal', 'usuarios.restore', {usuario: {{ $usuario['id'] }}})" />
                                        </li>
                                    @endcan
                                @endif
                            </ul>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ user()->cliente_id ? '4' : '5' }}">
                            <div class="list-group-item">
                                {{ __('site.common.results_not_found') }}
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination :links="$usuarios" :count="true" />
</div>
