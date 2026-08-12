<?php

namespace App\Livewire\Modulos;

use App\Livewire\Layouts\Modal;
use App\Models\Modulo;

class Delete extends Modal
{
    public Modulo $modulo;

    public function render()
    {
        return view('livewire.modulos.delete');
    }

    public function init()
    {
        if (user()->cannot('delete', $this->modulo)) {
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            $this->dispatch('closeModal');
            return;
        }
    }

    public function delete()
    {
        $this->modulo->paquetes()->detach();
        $this->modulo->suscripciones()->detach();
        $this->modulo->delete();

        $this->dispatch('show-toast', __('site.modules.delete.module_deactivated'));
        $this->dispatch('$refresh');
        $this->dispatch('closeModal');
    }
}
