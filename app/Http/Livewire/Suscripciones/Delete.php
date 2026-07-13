<?php

namespace App\Http\Livewire\Suscripciones;

use App\Http\Livewire\Layouts\Modal;
use App\Jobs\SendEmailJob;
use App\Models\ClaveProdServ;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Suscripcion;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;

class Delete extends Modal
{
    public $scope;
    public Suscripcion $suscripcion;
    public $motivo_desactivacion;

    public $selectedSubscriptionData = [
        'id' => null,
        'cliente_nombre' => '',
        'plan_nombre' => '',
    ];

    public function mount()
    {
        $this->selectedSubscriptionData = [
            'id' => $this->suscripcion->id,
            'cliente_nombre' => Crypt::decrypt($this->suscripcion->cliente->nombre_comercial),
            'plan_nombre' => $this->suscripcion->paquete->nombre,
        ];
    }

    public function render()
    {
        return view('livewire.suscripciones.delete');
    }

    public function init()
    {
        if (user()->cannot('deactivate', $this->suscripcion)) {
            $this->emit('show-toast', __('site.common.client_no_permissions'), 'danger');
            $this->emit('closeModal');
            return;
        }
    }

    public function desactivar()
    {
        $data = $this->validate(['motivo_desactivacion' => 'required|string|min:50']);
        $data['estado'] = 'INACTIVA';

        $this->suscripcion->fill($data)->save();

        $this->emit('show-toast', __('site.subscriptions.delete.subscription_delete_successfully'));

        if($this->scope)
            $this->emitTo($this->scope, 'suscripcion-deactivated', $this->suscripcion->id);

        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
