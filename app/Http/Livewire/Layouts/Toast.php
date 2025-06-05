<?php

namespace App\Http\Livewire\Layouts;

use Livewire\Component;

class Toast extends Component
{
    protected $listeners = ['show-toast' => 'setToast'];
    public $alertTypeClasses = [
        'info' => ' alert alert-info',
        'success' => ' alert alert-success',
        'warning' => ' alert alert-warning',
        'danger' => ' alert alert-danger',
    ];

    public $icons = [
        'info' => 'info-circle',
        'success' => 'check-circle',
        'warning' => 'exclamation-triangle',
        'danger' => 'exclamation-octagon',
    ];

    public $message;
    public $alertType = 'success';
    public $timeout;

    public function setToast($message = 'Mensaje de Notificación', string $alertType = 'success', int $timeout = 5000)
    {
        $this->message = is_array($message) ? join(" <br> ", $message) : "$message <br>";
        $this->alertType = $alertType;
        $this->timeout = $timeout;

        $this->dispatchBrowserEvent('toast-message-show');
    }

    public function render()
    {
        return view('livewire.layouts.toast');
    }
}
