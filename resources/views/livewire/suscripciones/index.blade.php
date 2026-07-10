@section('title', __('site.subscriptions.index.title'))

<div wire:init="init">
    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row justify-content-between">
        <div class="col-lg-auto mb-3">
            <div class="input-group">
                <span class="input-group-text"><x-icon name="search" /></span>
                <input type="search" placeholder="{{__('site.subscriptions.index.search_subscriptions')}}" class="form-control"
                    wire:model.debounce.500ms="search">
            </div>
        </div>
        <div class="col-lg-auto mb-3">
            @can('create', [App\Models\Suscripcion::class])
                <a href="{{ route('admin.suscripciones.save') }}" class="btn btn-site-primary btn-outline-warning">
                    <x-icon name="plus-lg" />
                    {{__('site.common.create')}}
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
                        <th class="text-left cursor-pointer text-uppercase" style="white-space: nowrap !important"
                            wire:click="changeSort('{{ $sort }}')">
                            <span>
                                @if ($this->sort == $sort)
                                    <i class="{{ $this->class_sort }}"></i>
                                @endif {{ $sort }}
                            </span>
                        </th>
                    @endforeach
                    <th class="text-center text-uppercase">{{__('site.common.actions')}}</th>
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
                        <td>${{ number_format($sub['total'], 2) }}</td>
                        <td class="text-center">
                            @php
                                $classEstado = 'dark';
                                $estado = __('site.subscriptions.index.no_status');
                                switch ($sub['estado']) {
                                    case 'PENDIENTE':
                                        $classEstado = 'primary';
                                        $estado = __('site.subscriptions.index.pending');
                                        break;
                                    case 'ACTIVA':
                                        $classEstado = 'success';
                                        $estado = __('site.subscriptions.index.active');
                                        break;
                                    case 'VENCIDA':
                                        $classEstado = 'warning';
                                        $estado = __('site.subscriptions.index.expired');
                                        break;
                                    case 'INACTIVA':
                                        $classEstado = 'danger';
                                        $estado = __('site.subscriptions.index.inactive');
                                        break;
                                }
                            @endphp
                            <span
                                class="badge bg-{{ $classEstado }}-subtle text-{{ $classEstado }} border-1 border-{{ $classEstado }}">
                                {{ $estado }}
                            </span>
                        </td>
                        <td class="text-center">
                            <ul class="list-unstyled mb-0">
                                @if (in_array($sub['estado'], ['PENDIENTE', 'ACTIVA']))
                                    @can('update', App\Models\Suscripcion::find($sub['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="pencil" title="{{__('site.common.edit')}}" :href="route('admin.suscripciones.save', $sub['id'])" />
                                        </li>
                                    @endcan
                                    @can('activate', App\Models\Suscripcion::find($sub['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="check2-square" title="{{__('site.common.activate')}}"
                                                click="$emit('openModal', 'suscripciones.activar', {'suscripcion': {{ $sub['id'] }}})" />
                                        </li>
                                    @endcan
                                    @if ($sub['estado'] == 'ACTIVA')
                                        <li class="list-inline-item">
                                            <x-action icon="file-pdf" title="{{__('site.common.download_pdf')}}"
                                                click="descargarPdf({{ $sub['id'] }})" />
                                        </li>
                                    @endif
                                    @can('revoke', App\Models\Suscripcion::find($sub['id']))
                                        <li class="list-inline-item">
                                            <x-action icon="trash" title="{{ __('site.common.deactivate') }}" />
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
                                {{__('site.common.results_not_found')}}
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination :links="$suscripciones" :count="true" />

    @if ($iframeContainerClass)
        <div class="modal {{ $iframeContainerClass }}">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{__('site.common.pdf')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            wire:click="$set('iframeContainerClass', '')"></button>
                    </div>
                    <div class="modal-body pb-0 text-center">
                        <div class="row">
                            <iframe src="{{ $iframeSrc }}" frameborder="0" id="frame-death-file"
                                height="500px"></iframe>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            wire:click="$set('iframeContainerClass', '')">{{ __('site.common.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
