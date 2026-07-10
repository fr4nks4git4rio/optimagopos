<?php

namespace App\Http\Livewire\Sucursales;

use App\Http\Livewire\Layouts\Modal;
use App\Models\Sucursal;

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
        $this->sucursal->suscripcion_id = null;
        $this->sucursal->save();
        $this->sucursal->delete();

        $this->emit('show-toast', __('site.branches.delete.branch_delete_success'));
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
