<?php

namespace App\Http\Livewire\Paquetes;

use App\Http\Livewire\Layouts\Modal;
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
            $this->emit('show-toast', __('site.common.client_no_permissions'), 'danger');
            $this->emit('closeModal');
            return;
        }
    }

    public function restore()
    {
        $paquete = Paquete::onlyTrashed()->find($this->paquete_id);
        if (!$paquete) {
            $this->emit('show-toast', __('site.packages.restore.package_not_found'), 'danger');
            $this->emit('closeModal');
            return;
        }
        $paquete->restore();

        $this->emit('show-toast', __('site.packages.restore.package_activated'));
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
