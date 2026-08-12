<?php

namespace App\Livewire\Paquetes;

use App\Livewire\Layouts\Modal;
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
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            $this->dispatch('closeModal');
            return;
        }
    }

    public function delete()
    {
        $this->paquete->delete();

        $this->dispatch('show-toast', __('site.packages.delete.package_deactivated'));
        $this->dispatch('$refresh');
        $this->dispatch('closeModal');
    }
}
