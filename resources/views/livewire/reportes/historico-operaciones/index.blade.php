@section('title', 'Histórico de Operaciones')

<div>
    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row justify-content-between">
        <div class="col-11 mb-3">
            <div class="row">
                <div class="col-md-2 col-6">
                    <x-input :label="'Fecha inicio'" type="date" :debounce="200" :lazy="true" model="fecha_inicio" />
                </div>
                <div class="col-md-2 col-6">
                    <x-input :label="'Fecha fin'" type="date" :debounce="200" :lazy="true" model="fecha_fin" />
                </div>
                <div class="col-md-4 col-12">
                    <x-select2-multiple class="form-control" :label="'Sucursales'" :lazy="true" model="sucursales"
                        :options="$sucursalesDisponibles" />
                </div>
                <div class="col-md-4 col-12">
                    <x-select2-multiple class="form-control" :label="'Terminales'" :dynamic="true" :lazy="true"
                        model="terminales" :options="$terminalesDisponibles" />
                </div>
                <div class="input-group">
                    <span class="input-group-text"><x-icon name="search" /></span>
                    <input type="search" placeholder="Buscar Tickets" class="form-control"
                        wire:model.debounce.500ms="search">
                </div>
            </div>
        </div>
        <div class="col-1 mb-3">
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
                        <th class="text-center cursor-pointer" style="white-space: nowrap !important"
                            wire:click="changeSort('{{ $sort }}')">
                            <span>
                                @if ($this->sort == $sort)
                                    <i class="{{ $this->class_sort }}"></i>
                                @endif {{ $sort }}
                            </span>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket['id_transaccion'] }}</td>
                        <td>{{ $ticket['fecha_transaccion_str'] }}</td>
                        <td>{{ $ticket['cliente'] }}</td>
                        <td>{{ $ticket['sucursal'] }}</td>
                        <td>{{ $ticket['terminal'] }}</td>
                        <td>{{ $ticket['empleado'] }}</td>
                        <td>{{ $ticket['ubicacion'] }}</td>
                        <td>{{ $ticket['productos'] }}</td>
                        <td>{{ $ticket['pagos'] }}</td>
                        <td>{{ $ticket['departamentos'] }}</td>
                        <td>${{ number_format($ticket['importe'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11">
                            <div class="list-group-item">
                                No se encontraron resultados...
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination :links="$tickets" :count="true" />
</div>
