<?php

namespace App\Http\Livewire\Sucursales;

use App\Http\Livewire\Layouts\Modal;
use App\Models\Sucursal;
use App\Models\SucursalFormaPago;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FormasPago extends Modal
{
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
            $this->emit('show-toast', 'No tiene permisos para realizar estar acción.', 'danger');
            $this->emit('closeModal');
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
        $this->modalFormaPagoSaveClass = 'show';
    }

    public function guardarFormPago()
    {
        $data = $this->validate([
            'forma_pago_activa.id' => 'nullable',
            'forma_pago_activa.nombre' => ['required'],
            'forma_pago_activa.moneda_id' => ['required', 'exists:tb_monedas,id']
        ], [
            'forma_pago_activa.nombre.required' => 'Campo requerido.',
            'forma_pago_activa.moneda.required' => 'Campo requerido.',
            'forma_pago_activa.moneda.exists' => 'Moneda no encontrada.',
        ]);

        if (
            DB::table('tb_sucursal_forma_pagos as sfp')
            ->leftJoin('tb_sucursales as s', 's.id', 'sfp.sucursal_id')
            ->where('sfp.nombre', $data['forma_pago_activa']['nombre'])
            ->where('sfp.id', '!=', $data['forma_pago_activa']['id'])
            ->where('s.cliente_id', $this->sucursal->cliente_id)
            ->count() > 0
        ) {
            $this->addError('forma_pago_activa.nombre', 'El nombre ya está en uso.');
            return;
        }

        if ($data['forma_pago_activa']['id'])
            $sfp = SucursalFormaPago::find($data['forma_pago_activa']['id']);
        else
            $sfp = new SucursalFormaPago();
        $sfp->fill(array_merge($data['forma_pago_activa'], ['sucursal_id' => $this->sucursal->id]))->save();

        $this->loadFormasPago();
        $this->index_forma_pago_activa = null;
        $this->forma_pago_activa = [
            'id' => null,
            'nombre' => '',
            'moneda_id' => null
        ];
        $this->modalFormaPagoSaveClass = '';
        $this->emit('show-toast', 'Forma de Pago guardada.');
    }

    public function showModalDeleteFormPago($index)
    {
        $this->index_forma_pago_activa = $index;
        $this->modalDeleteFormaPagoClass = 'show';
    }

    public function deleteFormaPago()
    {
        if ($this->index_forma_pago_activa !== null) {
            SucursalFormaPago::where('id', $this->formas_pago[$this->index_forma_pago_activa]['id'])->delete();
            $this->emit('show-toast', 'Forma de pago desactivada.');
            $this->loadFormasPago();
        }
        $this->modalDeleteFormaPagoClass = '';
    }

    public function showModalRestoreFormPago($index)
    {
        $this->index_forma_pago_activa = $index;
        $this->modalRestoreFormaPagoClass = 'show';
    }

    public function restoreFormaPago()
    {
        if ($this->index_forma_pago_activa !== null) {
            SucursalFormaPago::where('id', $this->formas_pago[$this->index_forma_pago_activa]['id'])->restore();
            $this->emit('show-toast', 'Forma de pago reactivada.');
            $this->loadFormasPago();
        }
        $this->modalRestoreFormaPagoClass = '';
    }
}
