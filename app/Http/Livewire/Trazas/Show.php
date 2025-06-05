<?php

namespace App\Http\Livewire\Trazas;

use App\Http\Livewire\Layouts\Modal;
use App\Models\Cliente;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

class Show extends Modal
{
    public Activity $log;

    public function mount() {}

    public function render()
    {
        return view('livewire.trazas.show');
    }

    public function getAttributes($attributes)
    {
        if ($this->log->subject_type == Cliente::class) {
            $attributes = Cliente::decryptInfo($attributes);
        } elseif ($this->log->subject_type == Sucursal::class) {
            $attributes = Sucursal::decryptInfo($attributes);
        }
        unset($attributes->decrypted);
        return $attributes;
    }

    public function plainText($label, $text = null)
    {
        if (in_array($label, ['cliente', 'sucursal']))
            return Crypt::decrypt($text);
        return $text;
    }
}
