<x-modal>
    <x-slot:title>
        Formas de Pago de Sucursal
    </x-slot:title>

    <x-slot:content>
        <div wire:init="init()" class="row">
            <div class="col-sm-12 mb-3">
                <div class="mb-1">
                    <label for="">Sucursal:</label>
                    <input type="text" class="form-control" value="{{ $this->nombre_sucursal }}" disabled>
                </div>
            </div>
            <div class="col-sm-12 table-responsive pb-2">
                <button type="button" class="btn btn-primary float-end" wire:click="showModalFormaPago()">Nueva Forma de
                    Pago</button>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Forma de Pago SAT</th>
                            <th class="text-center">Moneda</th>
                            <th class="text-center">Activa</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($formas_pago as $key => $forma_pago)
                            <tr>
                                <td>{{ $forma_pago['nombre'] }}</td>
                                <td>{{ $forma_pago['forma_pago_sat'] }}</td>
                                <td class="text-center">{{ $forma_pago['moneda'] }}</td>
                                <td class="text-center">
                                    @if ($forma_pago['deleted_at'])
                                        <span class="text-danger">NO</span>
                                    @else
                                        <span class="text-success">SI</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <ul class="list-unstyled mb-0">
                                        @if ($forma_pago['deleted_at'])
                                            <li class="list-inline-item mb-1">
                                                <x-action icon="check-circle" title="Reactivar"
                                                    click="showModalRestoreFormPago('{{ $key }}')" />
                                            </li>
                                        @else
                                            <li class="list-inline-item mb-1">
                                                <x-action icon="pencil" title="Editar"
                                                    click="showModalFormaPago('{{ $key }}')" />
                                            </li>
                                            <li class="list-inline-item mb-1">
                                                <x-action icon="trash" title="Desactivar"
                                                    click="showModalDeleteFormPago('{{ $key }}')" />
                                            </li>
                                        @endif
                                    </ul>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center" colspan="5">
                                    Sin datos que mostrar
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($modalFormaPagoSaveClass)
            <div class="modal {{ $modalFormaPagoSaveClass }}" id="modal-forma-pago-save">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title">{{ $index_forma_pago_activa !== null ? 'Editar ' : 'Nueva ' }}Forma
                                de Pago</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                wire:click="$set('modalFormaPagoSaveClass', '')"></button>
                        </div>
                        <div class="modal-body pb-0">
                            <div class="row">
                                @if ($index_forma_pago_activa !== null)
                                    <x-alert icon="exclamation-triangle" alert="info">
                                        <strong>Importante:</strong> Tenga en cuenta que, cualquier cambio en el
                                        <strong>nombre</strong>, deberá también aplicarse en las terminales de la
                                        Sucursal!
                                    </x-alert>
                                @endif
                                <x-input label="Nombre" model="forma_pago_activa.nombre" />
                                @if ($index_forma_pago_activa !== null)
                                    <x-select2-modals label="Forma Pago SAT" :options="$formasPagoOptions"
                                        model="forma_pago_activa.forma_pago_id" class="form-control" disabled />
                                    <div class="mb-1">
                                        <label for="">Moneda:</label>
                                        <select class="form-control" wire:model="forma_pago_activa.moneda" disabled>
                                            <option value="">Seleccione</option>
                                            @foreach ($monedas as $moneda)
                                                <option value="{{ $moneda }}">{{ $moneda }}</option>
                                            @endforeach
                                        </select>
                                        @error('forma_pago_activa.moneda')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @else
                                    <x-select2-modals label="Forma Pago SAT" :options="$formasPagoOptions"
                                        model="forma_pago_activa.forma_pago_id" class="form-control" />
                                    <div class="mb-1">
                                        <label for="">Moneda:</label>
                                        <select class="form-control" wire:model="forma_pago_activa.moneda">
                                            <option value="">Seleccione</option>
                                            @foreach ($monedas as $moneda)
                                                <option value="{{ $moneda }}">{{ $moneda }}</option>
                                            @endforeach
                                        </select>
                                        @error('forma_pago_activa.moneda')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                wire:click="$set('modalFormaPagoSaveClass', '')">{{ __('Cerrar') }}</button>
                            <button type="button" class="btn btn-primary" wire:click="guardarFormPago()">Guardar Forma
                                de Pago</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="modal {{ $modalRestoreFormaPagoClass }}">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            wire:click="$set('modalRestoreFormaPagoClass', '')"></button>
                    </div>
                    <div class="modal-body pb-0 text-center">
                        <x-alert icon="exclamation-octagon" alert="danger">
                            Seguro/a que desea reactivar la Forma de Pago?
                        </x-alert>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            wire:click="$set('modalRestoreFormaPagoClass', '')">{{ __('Cerrar') }}</button>
                        <button type="button" class="btn btn-primary" wire:click="restoreFormaPago()">Reactivar Forma
                            de
                            Pago</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal {{ $modalDeleteFormaPagoClass }}">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            wire:click="$set('modalDeleteFormaPagoClass', '')"></button>
                    </div>
                    <div class="modal-body pb-0 text-center">
                        <x-alert icon="exclamation-octagon" alert="danger">
                            Seguro/a que desea desactivar la Forma de Pago?
                        </x-alert>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            wire:click="$set('modalDeleteFormaPagoClass', '')">{{ __('Cerrar') }}</button>
                        <button type="button" class="btn btn-danger" wire:click="deleteFormaPago()">Desactivar Forma
                            de
                            Pago</button>
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
