@section('title', 'Usuarios')

<div>
    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row justify-content-between">
        <div class="col-lg-auto mb-3">
            <div class="input-group">
                <span class="input-group-text"><x-icon name="search" /></span>
                <input type="search" placeholder="Buscar Usuarios"
                    class="form-control" wire:model.debounce.500ms="search">
            </div>
        </div>
        <div class="col-lg-auto mb-3">
            <button type="button" class="btn btn-site-primary btn-outline-warning"
                wire:click="$emit('openModal', 'usuarios.save')">
                <x-icon name="plus-lg" />
                Crear
            </button>

            <x-dropdown icon="eye" :label="__($perPage)">
                @foreach($perPages as $perPage)
                <x-dropdown-item label="{{$perPage}}" click="$set('perPage', '{{ $perPage }}')" />
                @endforeach
            </x-dropdown>

            <x-dropdown icon="sort-down-alt" :label="__($sort)">
                @foreach($sorts as $sort)
                <x-dropdown-item :label="__($sort)" click="$set('sort', '{{ $sort }}')" />
                @endforeach
            </x-dropdown>

            <x-dropdown icon="filter" :label="__($filter)">
                @foreach($filters as $filter)
                <x-dropdown-item :label="__($filter)" click="$set('filter', '{{ $filter }}')" />
                @endforeach
            </x-dropdown>
        </div>
    </div>

    <div class="list-group mb-3 table-responsive">
        <table class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Nombre Completo</th>
                    <th>Correo</th>
                    @if (user()->is_super_admin)
                    <th>Cliente</th>
                    @endif
                    <th class="text-center" style="width: 100px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usuario)
                <tr>
                    <td class="text-center">
                        <img src="{{$usuario['avatar'] ? asset("avatars/{$usuario['avatar']}") : asset('img/avatars/no_avatar.png')}}" alt="Foto Usuario"
                            style="width: 80px" class="img-thumbnail">
                    </td>
                    <td>{{$usuario['nombre']}}</td>
                    <td>{{$usuario['email']}}</td>
                    @if (user()->is_super_admin)
                    <td>{{$usuario['cliente']}}</td>
                    @endif
                    <td class="text-center">
                        <ul class="list-unstyled mb-0">
                            @if(!$usuario['deleted_at'])
                            <li class="list-inline-item">
                                <x-action icon="pencil" title="Modificar"
                                    click="$emit('openModal', 'usuarios.save', {user: {{$usuario['id']}}})" />
                            </li>
                            @if((user()->is_super_admin && user()->id != $usuario['id']) || (user()->is_admin && user()->id != $usuario['id']))
                            <li class="list-inline-item">
                                <x-action icon="trash" title="Desactivar"
                                    click="$emit('openModal', 'usuarios.delete', {usuario: {{$usuario['id']}}})" />
                            </li>
                            @endif
                            @else
                            <li class="list-inline-item">
                                <x-action icon="check-circle" title="Reactivar"
                                    click="$emit('openModal', 'usuarios.restore', {usuario: {{$usuario['id']}}})" />
                            </li>
                            @endif
                        </ul>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ user()->is_super_admin ? '5' : '4' }}">
                        <div class="list-group-item">
                            No se encontraron resultados...
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination :links="$usuarios" :count="true" />
</div>
