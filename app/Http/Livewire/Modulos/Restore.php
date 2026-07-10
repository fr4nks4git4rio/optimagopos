<?php

namespace App\Http\Livewire\Modulos;

use App\Http\Livewire\Layouts\Modal;
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
            $this->emit('show-toast', __('site.common.client_no_permissions'), 'danger');
            $this->emit('closeModal');
            return;
        }
    }

    public function restore()
    {
        $modulo = Modulo::onlyTrashed()->find($this->modulo_id);
        if (!$modulo) {
            $this->emit('show-toast', __('site.modules.restore.module_not_found'), 'danger');
            $this->emit('closeModal');
            return;
        }
        $modulo->restore();

        $this->emit('show-toast', __('site.modules.restore.module_activated'));
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
