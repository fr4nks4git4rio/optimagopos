@section('title', 'Trazas')

<div>
    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row justify-content-between">
        <div class="col-lg-auto mb-3">
            <div class="input-group">
                <span class="input-group-text"><x-icon name="search"/></span>
                <input type="search" placeholder="Buscar Trazas"
                       class="form-control" wire:model.debounce.500ms="search">
            </div>
        </div>
        <div class="col-lg-auto mb-3">

            <x-dropdown icon="sort" :label="__($sort)">
                @foreach($sorts as $sort)
                    <x-dropdown-item :label="__($sort)" click="$set('sort', '{{ $sort }}')"/>
                @endforeach
            </x-dropdown>
        </div>
    </div>

    <div class="list-group mb-3 table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Nombre Log</th>
                <th>Descripción</th>
                <th>Realizado por</th>
                <th>Fecha</th>
                <th class="text-center">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @forelse($trazas as $log)
                <tr>
                    <td>{!! $log->log_name !!}</td>
                    <td>{!! $log->description !!}</td>
                    <td>{!! $log->causer_name !!}</td>
                    <td>{{$log->created_at}}</td>
                    <td class="text-center">
                        <ul class="list-unstyled mb-0">
                            <li class="list-inline-item">
                                <x-action icon="eye" :title="'Detalles'"
                                              click="$emit('openModal', 'trazas.show', {log: {{ $log->id }}})"/>
                            </li>
                        </ul>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <div class="list-group-item">
                            No se encontraron resultados...
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination :links="$trazas" :count="true"/>
</div>
