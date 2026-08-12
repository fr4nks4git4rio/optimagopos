<?php

namespace App\Livewire\Sucursales;

use App\Livewire\Layouts\Modal;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Crypt;

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
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            $this->dispatch('closeModal');
            return;
        }
    }

    public function restore()
    {
        $sucursal = Sucursal::onlyTrashed()->find($this->sucursal_id);
        if (!$sucursal) {
            $this->dispatch('show-toast', __('site.branches.restore.branch_not_found'), 'danger');
            $this->dispatch('closeModal');
            return;
        }
        $sucursal->restore();

        $attributes = $sucursal->getAttributes();

        activity(__('site.branches.delete.log_restored'))
            ->on($sucursal)
            ->event('restored')
            ->withProperties(Sucursal::parseData($attributes))
            ->log(__('site.branches.delete.log_restored_detail',  ['nombre_comercial' => Crypt::decrypt($attributes['nombre_comercial'])]));

        $this->dispatch('show-toast', __('site.branches.restore.branch_restore_success'));
        $this->dispatch('$refresh');
        $this->dispatch('closeModal');
    }
}
