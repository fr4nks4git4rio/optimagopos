<?php

namespace App\Http\Livewire\Sucursales;

use App\Http\Livewire\Layouts\Modal;
use App\Models\Sucursal;

class Restore extends Modal
{
    public $sucursal_id;

    public function render()
    {
        return view('livewire.sucursales.restore');
    }

    public function init()
    {
        if (user()->cannot('restore', Sucursal::withTrashed()->find($this->sucursal_id))) {
            $this->emit('show-toast', __('site.common.client_no_permissions'), 'danger');
            $this->emit('closeModal');
            return;
        }
    }

    public function restore()
    {
        $sucursal = Sucursal::onlyTrashed()->find($this->sucursal_id);
        if (!$sucursal) {
            $this->emit('show-toast', __('site.branches.restore.branch_not_found'), 'danger');
            $this->emit('closeModal');
            return;
        }
        $sucursal->restore();

        $this->emit('show-toast', __('site.branches.restore.branch_restore_success'));
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
