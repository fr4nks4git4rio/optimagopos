@section('title', 'Sucursales')

<div>
    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row justify-content-between">
        <div class="col-lg-auto mb-3">
            <div class="input-group">
                <span class="input-group-text"><x-icon name="search" /></span>
                <input type="search" placeholder="Buscar Sucursales"
                    class="form-control" wire:model.debounce.500ms="search">
            </div>
        </div>
        <div class="col-lg-auto mb-3">
            <button type="button" class="btn btn-site-primary btn-outline-warning"
                wire:click="$emit('openModal', 'sucursales.save')">
                <x-icon name="plus-lg" />
                Crear
            </button>

            <x-dropdown icon="eye" :label="__($perPage)">
                @foreach($perPages as $perPage)
                @if($perPage == $this->perPage)
                <x-dropdown-item label="{{$perPage}}" class="active" click="$set('perPage', '{{ $perPage }}')" />
                @else
                <x-dropdown-item label="{{$perPage}}" click="$set('perPage', '{{ $perPage }}')" />
                @endif
                @endforeach
            </x-dropdown>

            <x-dropdown icon="sort-down-alt" :label="__($sort)">
                @foreach($sorts as $sort)
                @if($sort == $this->sort)
                <x-dropdown-item :label="__($sort)" class="active" click="$set('sort', '{{ $sort }}')" />
                @else
                <x-dropdown-item :label="__($sort)" click="$set('sort', '{{ $sort }}')" />
                @endif
                @endforeach
            </x-dropdown>

            <x-dropdown icon="filter" :label="__($filter)">
                @foreach($filters as $filter)
                @if($this->filter == $filter)
                <x-dropdown-item :label="__($filter)" class="active" click="$set('filter', '{{ $filter }}')" />
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
                    <th>Logo</th>
                    <th>Nombre Comercial</th>
                    <th>Razón Social</th>
                    <th>Teléfono</th>
                    @if (user()->is_super_admin)
                    <th>Cliente</th>
                    @endif
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sucursales as $sucursal)
                <tr>
                    <td class="text-center">
                        <img src="{{$sucursal['logo'] ? asset("logos/{$sucursal['logo']}") : asset('img/no_image.png')}}" alt="Logo Sucursal"
                            style="width: 80px" class="img-thumbnail">
                    </td>
                    <td>{{$sucursal['nombre_comercial']}}</td>
                    <td>{{$sucursal['razon_social']}}</td>
                    <td>{{$sucursal['telefono']}}</td>
                    @if (user()->is_super_admin)
                    <td>{{$sucursal['cliente']}}</td>
                    @endif
                    <td class="text-center">
                        <ul class="list-unstyled mb-0">
                            @if(!$sucursal['deleted_at'])
                            <li class="list-inline-item">
                                <x-action icon="pencil" title="Modificar"
                                    click="$emit('openModal', 'sucursales.save', {sucursal : {{$sucursal['id']}}})" />
                            </li>
                            <li class="list-inline-item">
                                <x-action icon="card-list" title="Formas de Pago"
                                    click="$emit('openModal', 'sucursales.formas-pago', {sucursal: {{$sucursal['id']}}})" />
                            </li>
                            <li class="list-inline-item">
                                <x-action icon="trash" title="Desactivar"
                                    click="$emit('openModal', 'sucursales.delete', {sucursal: {{$sucursal['id']}}})" />
                            </li>
                            @else
                            <li class="list-inline-item">
                                <x-action icon="check-circle" title="Reactivar"
                                    click="$emit('openModal', 'sucursales.restore', {sucursal_id: {{$sucursal['id']}}})" />
                            </li>
                            @endif
                        </ul>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ user()->is_super_admin ? 6 : 5 }}">
                        <div class="list-group-item">
                            No se encontraron resultados...
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination :links="$sucursales" :count="true" />
</div>
