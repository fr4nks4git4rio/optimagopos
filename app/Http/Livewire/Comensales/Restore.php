<?php

namespace App\Http\Livewire\Comensales;

use App\Http\Livewire\Layouts\Modal;
use App\Models\Cliente;

class Restore extends Modal
{
    public $comensal_id;

    public function render()
    {
        return view('livewire.comensales.restore');
    }

    public function restore()
    {
        $comensal = Cliente::onlyTrashed()->find($this->comensal_id);
        if(!$comensal) {
            $this->emit('show-toast', 'Cliente no encontrado.', 'danger');
            $this->emit('closeModal');
            return;
        }
        $comensal->restore();

        $this->emit('show-toast', 'Cliente reactivado.');
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
