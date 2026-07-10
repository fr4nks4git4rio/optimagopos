<?php

namespace App\Http\Livewire\Paquetes;

use App\Http\Livewire\Layouts\Modal;
use App\Models\Paquete;

class Delete extends Modal
{
    public Paquete $paquete;

    public function render()
    {
        return view('livewire.paquetes.delete');
    }

    public function init()
    {
        if (user()->cannot('delete', $this->paquete)) {
            $this->emit('show-toast', __('site.common.client_no_permissions'), 'danger');
            $this->emit('closeModal');
            return;
        }
    }

    public function delete()
    {
        $this->paquete->delete();

        $this->emit('show-toast', __('site.packages.delete.package_deactivated'));
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
