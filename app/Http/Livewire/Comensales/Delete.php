<?php

namespace App\Http\Livewire\Comensales;

use App\Http\Livewire\Layouts\Modal;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

class Delete extends Modal
{
    public Cliente $comensal;

    public function render()
    {
        return view('livewire.comensales.delete');
    }

    public function init()
    {
        if (user()->cannot('deleteComensal', $this->comensal)) {
            $this->emit('show-toast', __('site.common.client_no_permissions'), 'danger');
            $this->emit('closeModal');
            return;
        }
    }

    public function delete()
    {

        DB::table('tb_clientes_comensales')
            ->where('cliente_id', user()->cliente_id)
            ->where('comensal_id', $this->comensal->id)
            ->update(['activo' => 0]);

        $this->emit('show-toast', __('site.diners.delete.client_delete_success'), 'success');
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
