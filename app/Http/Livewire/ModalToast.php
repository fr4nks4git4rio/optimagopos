<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Layouts\Modal;

class ModalToast extends Modal
{
    public $title = 'Alerta';
    public $messages = [];

    public function render()
    {
        return view('livewire.modal-toast');
    }

    public function close()
    {
        $this->emit('closeModal');
    }

    public function iconMessage($type)
    {
        switch ($type) {
            case 'success':
                return 'check-circle';
            case 'info':
                return 'exclamation-circle';
            case 'warning':
                return 'exclamation-diamond';
            case 'danger':
                return 'exclamation-octagon';
            default:
                return 'exclamation';
        }
    }
}
