@section('title', __('site.terminals.index.title'))

<div wire:init="init">
    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row justify-content-between">
        <div class="col-lg-auto mb-3">
            <div class="input-group">
                <span class="input-group-text"><x-icon name="search" /></span>
                <input type="search" placeholder="{{ __('site.terminals.index.search_terminals') }}" class="form-control"
                    wire:model.debounce.500ms="search">
            </div>
        </div>
        <div class="col-lg-auto mb-3">
            @can('create', [App\Models\Terminal::class])
                @if (user()->cliente_id)
                    <button type="button" class="btn btn-site-primary btn-outline-warning"
                        wire:click="$emit('openModal', 'terminales.save')">
                        <x-icon name="plus-lg" />
                        {{ __('site.common.create') }}
                    </button>
                @else
                    <button type="button" class="btn btn-site-primary btn-outline-warning"
                        wire:click="$emit('openModal', 'terminales.save-system')">
                        <x-icon name="plus-lg" />
                        {{ __('site.common.create') }}
                    </button>
                @endif
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
                                <span class="badge bg-primary-subtle text-primary text-uppercase">{{ __('site.common.yes') }}</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger text-uppercase">{{ __('site.common.no') }}</span>
                            @endif
                        </td>
                        <td>{{ $terminal['sucursal'] }}</td>
                        <td>{{ $terminal['comentarios'] }}</td>
                        <td class="text-center">
                            <ul class="list-unstyled mb-0">
                                @if (!$terminal['deleted_at'])
                                    @can('update', App\Models\Terminal::find($terminal['id']))
                                        @if (user()->cliente_id)
                                            <li class="list-inline-item">
                                                <x-action icon="pencil" title="{{ __('site.common.edit') }}"
                                                    click="$emit('openModal', 'terminales.save', {terminal: {{ $terminal['id'] }}})" />
                                            </li>
                                        @else
                                            <li class="list-inline-item">
                                                <x-action icon="pencil" title="{{ __('site.common.edit') }}"
                                                    click="$emit('openModal', 'terminales.save-system', {terminal: {{ $terminal['id'] }}})" />
                                            </li>
                                        @endif
                                    @endcan
                                    @can('delete', App\Models\Terminal::find($terminal['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="trash" title="{{ __('site.common.deactivate') }}"
                                                click="$emit('openModal', 'terminales.delete', {terminal: {{ $terminal['id'] }}})" />
                                        </li>
                                    @endcan
                                @else
                                    @can('restore', App\Models\Terminal::withTrashed()->find($terminal['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="check-circle" title="{{ __('site.common.restore') }}"
                                                click="$emit('openModal', 'terminales.restore', {terminal: {{ $terminal['id'] }}})" />
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

    <x-pagination :links="$terminales" :count="true" />
</div>
