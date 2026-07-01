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
            $this->emit('show-toast', 'No tiene permisos para realizar estar acción.', 'danger');
            $this->emit('closeModal');
            return;
        }
    }

    public function restore()
    {
        $modulo = Modulo::onlyTrashed()->find($this->modulo_id);
        if (!$modulo) {
            $this->emit('show-toast', 'Módulo no encontrado.', 'danger');
            $this->emit('closeModal');
            return;
        }
        $modulo->restore();

        $this->emit('show-toast', 'Módulo reactivado.');
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
