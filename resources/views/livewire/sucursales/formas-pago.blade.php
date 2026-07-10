<x-modal>
    <x-slot:title>
        {{ __('site.branches.branch_payment_forms.title') }}
    </x-slot:title>

    <x-slot:content>
        <div wire:init="init" class="row">
            <div class="col-sm-12 mb-3">
                <div class="mb-1">
                    <label for="">{{ __('site.branches.branch_payment_forms.branch') }}:</label>
                    <input type="text" class="form-control" value="{{ $this->nombre_sucursal }}" disabled>
                </div>
            </div>
            <div class="col-sm-12 table-responsive pb-2">
                <button type="button" class="btn btn-primary float-end" wire:click="showModalFormaPago()">
                    {{ __('site.branches.branch_payment_forms.new_payment_form') }}
                </button>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ __('site.branches.branch_payment_forms.name') }}</th>
                            <th class="text-center">{{ __('site.branches.branch_payment_forms.currency') }}</th>
                            <th class="text-center">{{ __('site.branches.branch_payment_forms.status') }}</th>
                            <th class="text-center">{{ __('site.common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($formas_pago as $key => $forma_pago)
                            <tr>
                                <td>{{ $forma_pago['nombre'] }}</td>
                                <td class="text-center">
                                    <span
                                        class="badge bg-primary-subtle text-primary">{{ $forma_pago['moneda'] }}</span>
                                </td>
                                <td class="text-center">
                                    @if ($forma_pago['deleted_at'])
                                        <span
                                            class="badge bg-danger-subtle text-danger border-1 border-danger">{{ __('site.common.inactive') }}</span>
                                    @else
                                        <span
                                            class="badge bg-success-subtle text-success border-1 border-success">{{ __('site.common.active') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <ul class="list-unstyled mb-0">
                                        @if ($forma_pago['deleted_at'])
                                            <li class="list-inline-item mb-1">
                                                <x-action icon="check-circle" title="{{ __('site.common.restore') }}"
                                                    click="showModalRestoreFormPago('{{ $key }}')" />
                                            </li>
                                        @else
                                            <li class="list-inline-item mb-1">
                                                <x-action icon="pencil" title="{{ __('site.common.edit') }}"
                                                    click="showModalFormaPago('{{ $key }}')" />
                                            </li>
                                            <li class="list-inline-item mb-1">
                                                <x-action icon="trash" title="{{ __('site.common.deactivate') }}"
                                                    click="showModalDeleteFormPago('{{ $key }}')" />
                                            </li>
                                        @endif
                                    </ul>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center" colspan="4">
                                    {{ __('site.common.results_not_found') }}
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
                            <h3 class="modal-title">
                                {{ $index_forma_pago_activa !== null ? __('site.branches.branch_payment_forms.edit_payment_form') : __('site.branches.branch_payment_forms.new_payment_form') }}
                            </h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                wire:click="$set('modalFormaPagoSaveClass', '')"></button>
                        </div>
                        <div class="modal-body pb-0">
                            <div class="row">
                                @if ($index_forma_pago_activa !== null)
                                    <div class="col-12">
                                        <x-alert icon="exclamation-triangle" alert="info">
                                            {!! __('site.branches.branch_payment_forms.changes_alert') !!}
                                        </x-alert>
                                    </div>
                                @endif
                                <x-input label="{{ __('site.branches.branch_payment_forms.name') }}" model="forma_pago_activa.nombre" />
                                @if ($index_forma_pago_activa !== null)
                                    <x-select2-modals label="{{ __('site.branches.branch_payment_forms.currency') }}" :options="$monedas"
                                        model="forma_pago_activa.moneda_id" class="form-control" disabled />
                                @else
                                    <x-select2-modals label="{{ __('site.branches.branch_payment_forms.currency') }}" :options="$monedas"
                                        model="forma_pago_activa.moneda_id" class="form-control" />
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                wire:click="$set('modalFormaPagoSaveClass', '')">{{ __('site.common.close') }}</button>
                            <button type="button" class="btn btn-primary" wire:click="guardarFormPago()">
                            {{ __('site.branches.branch_payment_forms.save_payment_form') }}
                            </button>
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
