<?php

namespace App\Http\Livewire\Clientes;

use App\Http\Livewire\Layouts\Modal;
use App\Models\Cliente;

class Delete extends Modal
{
    public Cliente $cliente;

    public function render()
    {
        return view('livewire.clientes.delete');
    }

    public function init()
    {
        if (user()->cannot('deleteCliente', $this->cliente)) {
            $this->emit('show-toast', 'No tiene permisos para realizar estar acción.', 'danger');
            $this->emit('closeModal');
            return;
        }
    }

    public function delete()
    {
        $this->cliente->delete();

        $this->emit('show-toast', 'Cliente desactivado.');
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
