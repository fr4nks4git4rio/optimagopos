<?php

namespace App\Http\Livewire\Layouts;

use Livewire\Component;

class Loading extends Component
{
    protected $listeners = ['show-loading' => 'setLoading'];

    public $message;
    public $close;

    public function setLoading($message = 'Cargando...', $close = false)
    {
        $this->message = $message;
        $this->close = $close;

        $this->dispatchBrowserEvent('loading-show');
    }

    public function render()
    {
        return view('livewire.layouts.loading');
    }
}
