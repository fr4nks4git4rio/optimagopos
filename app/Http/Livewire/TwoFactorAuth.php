<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Http\Livewire\Layouts\Modal;
use App\Models\User;

class TwoFactorAuth extends Modal
{
    public $code;

    public static function modalMaxWidthClass(): string
    {
        return 'modal-md';
    }

    public function sendCode($resend = false)
    {
        $code = rand(100000, 999999); // Generar un código de 6 dígitos

        $user = User::find(user()->id);
        // Guardar el código en la db
        $user->two_factor_code = $code;
        $user->save();

        // Enviar el código por correo
        if (config('app.env') != 'local')
            Mail::to(user()->email)->send(new \App\Mail\TwoFactorCode($code));
        else
            Log::error("Código de Verificación: " . $code);
        if ($resend) {
            $this->emit('show-toast', 'Código de Verificación enviado!');
        }
    }

    public function verifyCode()
    {
        $this->validate([
            'code' => 'required|size:6',
        ], [
            'code.required' => 'Por favor introduzca el código de verificación',
            'code.size' => 'El código de verificación debe tener 6 dígitos',
        ]);

        if ($this->code == user()->two_factor_code) {
            $user = User::find(user()->id);
            $user->two_factor_code = null;
            $user->last_authenticated_ip = request()->ip() ?: 'local';
            $user->save();
            return redirect()->route('home'); // Redirigir al usuario a la página de inicio
        }

        $this->addError('code', 'El código proporcionado es incorrecto.');
    }

    public function render()
    {
        return view('livewire.two-factor-auth');
    }
}
