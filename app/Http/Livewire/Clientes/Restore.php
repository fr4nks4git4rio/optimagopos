<?php

namespace App\Http\Livewire\Clientes;

use App\Http\Livewire\Layouts\Modal;
use App\Models\Cliente;

class Restore extends Modal
{
    public $cliente_id;

    public function render()
    {
        return view('livewire.clientes.restore');
    }

    public function init()
    {
        if (user()->cannot('restoreCliente', Cliente::withTrashed()->find($this->cliente_id))) {
            $this->emit('show-toast', 'No tiene permisos para realizar estar acción.', 'danger');
            $this->emit('closeModal');
            return;
        }
    }

    public function restore()
    {
        $cliente = Cliente::onlyTrashed()->find($this->cliente_id);
        if (!$cliente) {
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
