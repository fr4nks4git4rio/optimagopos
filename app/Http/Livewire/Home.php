<?php

namespace App\Http\Livewire;

use App\Models\Administracion\CodificadoresGenerales\Operador;
use App\Models\Administracion\CodificadoresGenerales\Unidad;
use App\Models\Administracion\TipoCambio;
use App\Models\User;
use App\Notifications\SiteNotification;
use Carbon\Carbon;
use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        return view('livewire.home');
    }

    public function init()
    {
        //        if (!user()->twoFactorAuthenticationVerified()) {
        //            session()->forget('two_factor_code');
        //            session()->forget('two_factor_verified');
        //            $this->emit('openModal', 'two-factor-auth');
        //        }
    }
}
