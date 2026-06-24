<?php

namespace App\Http\Livewire\Comensales;

use App\Http\Livewire\Layouts\Modal;
use App\Models\Cliente;

class Delete extends Modal
{
    public Cliente $comensal;

    public function render()
    {
        return view('livewire.comensales.delete');
    }

    public function init(){
        if (user()->cannot('deleteComensal', $this->comensal)) {
            $this->emit('show-toast', 'No tiene permisos para realizar estar acción.', 'danger');
            $this->emit('closeModal');
            return;
        }
    }

    public function delete()
    {
        $this->comensal->delete();

        $this->emit('show-toast', 'Cliente desactivado.');
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
