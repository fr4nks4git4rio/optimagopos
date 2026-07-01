<?php

namespace App\Http\Livewire\Modulos;

use App\Http\Livewire\Layouts\Modal;
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
            $this->emit('show-toast', 'No tiene permisos para realizar estar acción.', 'danger');
            $this->emit('closeModal');
            return;
        }
    }

    public function delete()
    {
        $this->modulo->delete();

        $this->emit('show-toast', 'Módulo desactivado.');
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
