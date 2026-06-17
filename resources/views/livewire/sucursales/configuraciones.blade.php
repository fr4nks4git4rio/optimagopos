<x-modal>
    <x-slot:title>
        Configuraciones de Sucursal
    </x-slot:title>

    <x-slot:content>
        <div class="row mb-3">
            <div class="col-sm-12 mb-3">
                <div class="mb-1">
                    <label for="">Sucursal:</label>
                    <input type="text" class="form-control" value="{{ $this->nombre_sucursal }}" disabled>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8 col-xs-12">
                <div class="card">
                    <div class="card-header pt-3 mb-0">
                        <h2 class="fs-4 mb-0">Tasas de Cambio</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-3 pt-1 mb-0 border-bottom">
                                <x-select2-component-modals class="form-control" label="Moneda Base"
                                    model="tipo_cambio.from_id" :options="$monedas" :dynamic="true" />
                            </div>
                            <div class="col-3 pt-1 pb-2 mb-0 border-bottom">
                                <x-select2-component-modals class="form-control" label="Moneda Destino"
                                    model="tipo_cambio.to_id" :options="$monedas" :dynamic="true" />
                            </div>
                            <div class="col-3 pt-1 mb-0 border-bottom">
                                <x-input label="Tasa de Cambio" model="tipo_cambio.tasa" />
                            </div>
                            <div class="col-3 pt-4 mb-0 border-bottom">
                                <button class="btn btn-site-primary" wire:click="saveTasa">Guardar Tasa</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Moneda Base</th>
                                        <th>Moneda Destino</th>
                                        <th>Tasa de Cambio</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tipos_cambio as $tc)
                                        <tr>
                                            <td>{{ $tc['from_moneda'] }}</td>
                                            <td>{{ $tc['to_moneda'] }}</td>
                                            <td>{{ $tc['tasa'] }}</td>
                                            <td class="text-center">
                                                <x-action icon="pencil" title="Modificar"
                                                    click="editRate({{ $tc['id'] }})" />
                                                <x-action icon="trash" title="Eliminar"
                                                    click="$emit('openModal', 'tipos-cambio.delete', {'tipoCambio': {{ $tc['id'] }}, 'scope': 'sucursales.configuraciones'})" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xs-12">
                <div class="card mb-2">
                    <div class="card-header pt-3 mb-0">
                        <h2 class="fs-4 mb-0">Otras Configuraciones</h2>
                    </div>
                    <div class="card-body">
                        <x-select2-component-modals class="form-control" label="Moneda de Sucursal"
                            model="moneda_sucursal" :options="$monedas" />
                    </div>
                </div>
            </div>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
            wire:click="$emit('closeModal')">Cerrar</button>
    </x-slot:buttons>
</x-modal>
