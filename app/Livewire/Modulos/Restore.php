<?php

namespace App\Livewire\Modulos;

use App\Livewire\Layouts\Modal;
use App\Models\Modulo;

class Restore extends Modal
{
    public $modulo_id;

    public function render()
    {
        return view('livewire.modulos.restore');
    }

    public function init()
    {
        if (user()->cannot('restore', Modulo::withTrashed()->find($this->modulo_id))) {
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            $this->dispatch('closeModal');
            return;
        }
    }

    public function restore()
    {
        $modulo = Modulo::onlyTrashed()->find($this->modulo_id);
        if (!$modulo) {
            $this->dispatch('show-toast', __('site.modules.restore.module_not_found'), 'danger');
            $this->dispatch('closeModal');
            return;
        }
        $modulo->restore();

        $this->dispatch('show-toast', __('site.modules.restore.module_activated'));
        $this->dispatch('$refresh');
        $this->dispatch('closeModal');
    }
}
