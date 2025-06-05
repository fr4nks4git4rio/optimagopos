<?php

namespace App\Http\Livewire\Auth;

use App\Models\Administracion\CodificadoresGenerales\Operador;
use App\Models\Administracion\CodificadoresGenerales\Unidad;
use App\Models\Administracion\TipoCambio;
use App\Models\ExchangeRate;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Component;

class Login extends Component
{
    public $email;
    public $password;
    public $remember;

    public function render()
    {
        return view('livewire.auth.login');
    }

    public function rules()
    {
        return [
            'email' => ['required'],
            'password' => ['required'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Debe entrar el correo.',
            'password.required' => 'Debe entrar la contraseña.'
        ];
    }

    public function login()
    {
        $data = $this->validate($this->rules(), $this->messages());

        $throttleKey = Str::lower($this->email) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $this->addError('email', __('auth.throttle', [
                'seconds' => RateLimiter::availableIn($throttleKey),
            ]));

            return;
        }

        if (!Auth::guard('web')->attempt(['email' => $this->email, 'password' => $this->password], $data['remember'])) {
            RateLimiter::hit($throttleKey);

            $this->addError('email', __('auth.failed'));
            return;
        }

        $user = User::find(user()->id);
        activity('Login Usuario')
            ->on($user)
            ->event('login')
            ->withProperties(Arr::except(
                $user->toArray(),
                ['password', 'created_at', 'updated_at', 'deleted_at']
            ))
            ->log("El usuario $user->email se ha autenticado.");

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
