<?php

namespace App\Http\Livewire\Comensales;

use App\Http\Livewire\Layouts\Modal;
use App\Models\Cliente;

class Restore extends Modal
{
    public $cliente_id;

    public function render()
    {
        return view('livewire.comensales.restore');
    }

    public function restore()
    {
        $cliente = Cliente::onlyTrashed()->find($this->cliente_id);
        if(!$cliente){
            $this->emit('show-toast', 'Cliente no encontrado.', 'danger');
            $this->emit('closeModal');
            return;
        }
        $cliente->restore();

        $this->emit('show-toast', 'Cliente reactivado.');
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
