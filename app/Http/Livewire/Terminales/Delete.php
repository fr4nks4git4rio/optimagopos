<?php

namespace App\Http\Livewire\Terminales;

use App\Http\Livewire\Layouts\Modal;
use App\Models\Terminal;
use Livewire\WithFileUploads;

class Delete extends Modal
{
    use WithFileUploads;

    public Terminal $terminal;

    public function render()
    {
        return view('livewire.terminales.delete');
    }

    public function init()
    {
        if (user()->cannot('delete', $this->terminal)) {
            $this->emit('show-toast', __('site.common.client_no_permissions'), 'danger');
            $this->emit('closeModal');
            return;
        }
    }

    public function delete()
    {
        $terminal = $this->terminal;
        $attributes = $terminal->getAttributes();

        $this->terminal->suscripcion_id = null;
        $this->terminal->save();
        $this->terminal->delete();

        activity(__('site.terminals.delete.log_deleted'))
            ->on($terminal)
            ->event('deleted')
            ->withProperties(Terminal::parseData($attributes))
            ->log(__('site.terminals.delete.log_deleted_detail',  ['name' => $attributes['nombre']]));

        $this->emit('show-toast', __('site.terminals.delete.terminal_delete_success'));
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
