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
            $this->emit('show-toast', __('site.common.client_no_permissions'), 'danger');
            $this->emit('closeModal');
            return;
        }
    }

    public function delete()
    {
        $this->cliente->delete();

        $this->emit('show-toast', __('site.clientes.delete.client_delete_success'));
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
