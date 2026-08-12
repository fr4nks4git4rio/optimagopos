<?php

namespace App\Livewire\Clientes;

use App\Livewire\Layouts\Modal;
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
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            $this->dispatch('closeModal');
            return;
        }
    }

    public function delete()
    {
        $this->cliente->delete();

        $this->dispatch('show-toast', __('site.clientes.delete.client_delete_success'));
        $this->dispatch('$refresh');
        $this->dispatch('closeModal');
    }
}
