<?php

namespace App\Livewire;

use Livewire\Component;

class ContextualHelp extends Component
{
    public $currentRoute;

    public function mount($currentRoute)
    {
        $this->currentRoute = $currentRoute;
    }

    public function render()
    {
        return view('livewire.contextual-help', [
            'terms' => $this->terms()
        ]);
    }

    public function terms()
    {
        switch ($this->currentRoute) {
            case 'home':
                return [];
            default:
                return [];
        }
    }
}
