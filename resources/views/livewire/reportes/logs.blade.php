@section('title', 'Logs')

<div>
    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row justify-content-between">
        <div class="col-lg-auto mb-3">
            <div class="input-group">
                <span class="input-group-text"><x-icon name="search" /></span>
                <input type="search" placeholder="Buscar Logs" class="form-control" wire:model.debounce.500ms="search">
            </div>
        </div>
        <div class="col-lg-auto mb-3">
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

            <x-dropdown icon="sort-down-alt" :label="__($sort)">
                @foreach ($sorts as $sort)
                    @if ($sort == $this->sort)
                        <x-dropdown-item :label="__($sort)" class="active" click="$set('sort', '{{ $sort }}')" />
                    @else
                        <x-dropdown-item :label="__($sort)" click="$set('sort', '{{ $sort }}')" />
                    @endif
                @endforeach
            </x-dropdown>
        </div>
    </div>

    <div class="list-group mb-3 table-responsive">
        <table class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Log</th>
                    <th>Datos</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->fecha }}</td>
                        <td>{{ $log->log }}</td>
                        <td>{{ $log->data }}</td>
                        <td>{{ $log->status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="list-group-item">
                                No se encontraron resultados...
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination :links="$logs" :count="true" />
</div>
