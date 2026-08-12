<?php

namespace App\Livewire\Clientes;

use App\Livewire\Layouts\Modal;
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
            $this->dispatch('show-toast', 'No tiene permisos para realizar estar acción.', 'danger');
            $this->dispatch('closeModal');
            return;
        }
    }

    public function restore()
    {
        $cliente = Cliente::onlyTrashed()->find($this->cliente_id);
        if (!$cliente) {
            $this->dispatch('show-toast', 'Cliente no encontrado.', 'danger');
            $this->dispatch('closeModal');
            return;
        }
        $cliente->restore();

        $this->dispatch('show-toast', 'Cliente reactivado.');
        $this->dispatch('$refresh');
        $this->dispatch('closeModal');
    }
}
