<?php

namespace App\Livewire\Paquetes;

use App\Livewire\Layouts\Modal;
use App\Models\Paquete;

class Restore extends Modal
{
    public $paquete_id;

    public function render()
    {
        return view('livewire.paquetes.restore');
    }

    public function init()
    {
        if (user()->cannot('restore', Paquete::withTrashed()->find($this->paquete_id))) {
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            $this->dispatch('closeModal');
            return;
        }
    }

    public function restore()
    {
        $paquete = Paquete::onlyTrashed()->find($this->paquete_id);
        if (!$paquete) {
            $this->dispatch('show-toast', __('site.packages.restore.package_not_found'), 'danger');
            $this->dispatch('closeModal');
            return;
        }
        $paquete->restore();

        $this->dispatch('show-toast', __('site.packages.restore.package_activated'));
        $this->dispatch('$refresh');
        $this->dispatch('closeModal');
    }
}
