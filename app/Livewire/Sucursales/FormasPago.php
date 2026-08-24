<?php

namespace App\Livewire\Sucursales;

use App\Livewire\Layouts\Modal;
use App\Models\Sucursal;
use App\Models\SucursalFormaPago;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FormasPago extends Modal
{
    public $scope;
    public Sucursal $sucursal;
    public $formas_pago = [];
    public $monedas = [];

    public $index_forma_pago_activa = null;

    public $forma_pago_activa = [
        'id' => null,
        'nombre' => '',
        'moneda_id' => null
    ];

    public $modalFormaPagoSaveClass = '';
    public $modalDeleteFormaPagoClass = '';
    public $modalRestoreFormaPagoClass = '';

    public function mount()
    {
        $this->monedas = DB::table('tb_monedas')
            ->select('id as value', 'acronimo as label')
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($value, $key) {
                return (array)$value;
            })->toArray();
    }

    public function render()
    {
        return view('livewire.sucursales.formas-pago');
    }

    public function getNombreSucursalProperty()
    {
        return Crypt::decrypt($this->sucursal->nombre_comercial);
    }

    public function init()
    {
        if (user()->cannot('setPaymentForms', $this->sucursal)) {
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            $this->dispatch('closeModal');
            return;
        }
        $this->loadFormasPago();
    }

    public function loadFormasPago()
    {
        $this->formas_pago = DB::table('tb_sucursal_forma_pagos as sfp')
            ->select(
                'sfp.id',
                'sfp.nombre',
                'moneda.acronimo as moneda',
                'sfp.moneda_id',
                'sfp.deleted_at'
            )
            ->leftJoin('tb_monedas as moneda', 'moneda.id', '=', 'sfp.moneda_id')
            ->where('sucursal_id', $this->sucursal->id)
            ->get()->map(function ($value, $key) {
                return (array)$value;
            })->toArray();
    }

    public function showModalFormaPago($index = null)
    {
        $this->index_forma_pago_activa = $index;
        if ($index !== null) {
            $this->forma_pago_activa = [
                'id' => $this->formas_pago[$index]['id'],
                'nombre' => $this->formas_pago[$index]['nombre'],
                'moneda_id' => $this->formas_pago[$index]['moneda_id']
            ];
        } else {
            $this->forma_pago_activa = [
                'id' => null,
                'nombre' => '',
                'moneda_id' => null
            ];
        }
        $this->dispatch('show-sub-modal', 'modal-forma-pago-save');
    }

    public function guardarFormPago()
    {
        $data = $this->validate(
            [
                'forma_pago_activa.id' => 'nullable',
                'forma_pago_activa.nombre' => ['required'],
                'forma_pago_activa.moneda_id' => ['required', 'exists:tb_monedas,id']
            ],
            //  [
            //     'forma_pago_activa.nombre.required' => 'Campo requerido.',
            //     'forma_pago_activa.moneda.required' => 'Campo requerido.',
            //     'forma_pago_activa.moneda.exists' => 'Moneda no encontrada.',
            // ]
        );

        if (
            DB::table('tb_sucursal_forma_pagos as sfp')
            ->leftJoin('tb_sucursales as s', 's.id', 'sfp.sucursal_id')
            ->where('sfp.nombre', $data['forma_pago_activa']['nombre'])
            ->where('sfp.id', '!=', $data['forma_pago_activa']['id'])
            ->where('s.id', $this->sucursal->id)
            ->count() > 0
        ) {
            $this->addError('forma_pago_activa.nombre', __('validation.unique', ['attribute' => 'nombre']));
            return;
        }

        if ($data['forma_pago_activa']['id'])
            $sfp = SucursalFormaPago::find($data['forma_pago_activa']['id']);
        else
            $sfp = new SucursalFormaPago();
        $sfp->fill(array_merge($data['forma_pago_activa'], ['sucursal_id' => $this->sucursal->id]))->save();

        if ($sfp->wasRecentlyCreated) {
            $attributes = Arr::except($sfp->getDirty(), ['created_at', 'updated_at', 'deleted_at']);
            $log = __('site.branches.branch_payment_forms.log_created_detail', ['nombre_comercial' => Crypt::decrypt($this->sucursal->nombre_comercial), 'payment_from' => $sfp->nombre]);
            activity(__('site.branches.branch_payment_forms.log_created'))
                ->on($sfp)
                ->event('created')
                ->withProperties(SucursalFormaPago::parseData($attributes))
                ->log($log);
            $this->dispatch('sucursal-created', $this->sucursal->id)->to($this->scope);
        } else {
            $attributes = Arr::except($sfp->getDirty(), ['created_at', 'updated_at', 'deleted_at']);
            $log = __('site.branches.branch_payment_forms.log_updated_detail', ['nombre_comercial' => Crypt::decrypt($this->sucursal->nombre_comercial), 'payment_from' => $sfp->nombre]);
            activity(__('site.branches.branch_payment_forms.log_updated'))
                ->on($sfp)
                ->event('updated')
                ->withProperty('attributes', Sucursal::parseData($attributes))
                ->withProperty('old', Sucursal::parseData(Arr::only($sfp->getOriginal(), array_keys($attributes))))
                ->log($log);
            $this->dispatch('sucursal-updated', $this->sucursal->id)->to($this->scope);
        }

        $this->loadFormasPago();
        $this->index_forma_pago_activa = null;
        $this->forma_pago_activa = [
            'id' => null,
            'nombre' => '',
            'moneda_id' => null
        ];
        $this->dispatch('hide-sub-modal', 'modal-forma-pago-save');
        $this->dispatch('show-toast', __('site.branches.branch_payment_forms.payment_form_saved'));
    }

    public function showModalDeleteFormPago($index)
    {
        $this->index_forma_pago_activa = $index;
        $this->dispatch('show-sub-modal', 'modal-delete-forma-pago');
    }

    public function deleteFormaPago()
    {
        if ($this->index_forma_pago_activa !== null) {
            $sfp = SucursalFormaPago::find($this->formas_pago[$this->index_forma_pago_activa]['id']);

            $attributes = Arr::except($sfp->getDirty(), ['created_at', 'updated_at', 'deleted_at']);

            $sfp->delete();

            $log = __('site.branches.branch_payment_forms.log_deleted_detail', ['nombre_comercial' => Crypt::decrypt($this->sucursal->nombre_comercial), 'payment_from' => $sfp->nombre]);
            activity(__('site.branches.branch_payment_forms.log_deleted'))
                ->on($sfp)
                ->event('deleted')
                ->withProperties(SucursalFormaPago::parseData($attributes))
                ->log($log);

            $this->dispatch('show-toast', __('site.branches.branch_payment_forms.payment_form_deactivated'));
            $this->loadFormasPago();
        }
        $this->dispatch('hide-sub-modal', 'modal-delete-forma-pago');
    }

    public function showModalRestoreFormPago($index)
    {
        $this->index_forma_pago_activa = $index;
        $this->dispatch('show-sub-modal', 'modal-restore-forma-pago');
    }

    public function restoreFormaPago()
    {
        if ($this->index_forma_pago_activa !== null) {
            SucursalFormaPago::where('id', $this->formas_pago[$this->index_forma_pago_activa]['id'])->restore();
            $this->dispatch('show-toast', __('site.branches.branch_payment_forms.payment_form_activated'));
            $this->loadFormasPago();
        }
        $this->dispatch('hide-sub-modal', 'modal-restore-forma-pago');
    }
}
