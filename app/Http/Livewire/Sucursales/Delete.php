<?php

namespace App\Http\Livewire\Sucursales;

use App\Http\Livewire\Layouts\Modal;
use App\Models\Sucursal;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;

class Delete extends Modal
{
    public Sucursal $sucursal;

    public function render()
    {
        return view('livewire.sucursales.delete');
    }

    public function init()
    {
        if (user()->cannot('delete', $this->sucursal)) {
            $this->emit('show-toast', __('site.common.client_no_permissions'), 'danger');
            $this->emit('closeModal');
            return;
        }
    }

    public function delete()
    {
        $model = $this->sucursal;
        $attributes = $model->getAttributes();

        $this->sucursal->suscripcion_id = null;
        $this->sucursal->save();
        $this->sucursal->delete();

        activity(__('site.branches.delete.log_deleted'))
            ->on($model)
            ->event('deleted')
            ->withProperties(Sucursal::parseData($attributes))
            ->log(__('site.branches.delete.log_deleted_detail',  ['nombre_comercial' => Crypt::decrypt($attributes['nombre_comercial'])]));

        $this->emit('show-toast', __('site.branches.delete.branch_delete_success'));
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
