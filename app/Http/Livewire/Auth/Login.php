<?php

namespace App\Http\Livewire\Auth;

use App\Jobs\SendEmailJob;
use App\Models\Administracion\CodificadoresGenerales\Operador;
use App\Models\Administracion\CodificadoresGenerales\Unidad;
use App\Models\Administracion\TipoCambio;
use App\Models\ExchangeRate;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
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
        $data = $this->validate(
            $this->rules(),
            // $this->messages()
        );

        $throttleKey = Str::lower($this->email) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $this->addError('email', __('auth.throttle', [
                'seconds' => RateLimiter::availableIn($throttleKey),
            ]));

            return;
        }

        if (! auth()->validate(Arr::only($data, ['email', 'password']))) {
            RateLimiter::hit($throttleKey);

            $this->addError('email', __('auth.failed'));
            return;
        }


        $user = User::where('email', $this->email)->first();

        if ($user->cliente_id && $user->suscripciones_activas()->count() == 0) {
            $this->addError('email', __('auth.subscription_failed'));
            return;
        }

        // 2. Comprobar si el dispositivo es de confianza
        $cookieName = 'device_trusted_' . $user->id;
        if (Cookie::has($cookieName)) {
            // Dispositivo de confianza: Loguear directo
            auth()->login($user, $data['remember']);
            RateLimiter::clear($throttleKey);

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

        // 3. Generar y enviar código 2FA
        $code = rand(100000, 999999);
        $expiresIn = 10;
        $user->update([
            'two_factor_code' => $code,
            'two_factor_expires_at' => now()->addMinutes($expiresIn),
        ]);

        SendEmailJob::dispatch(
            recipients: $user->email,
            from_email: '',
            from_name: '',
            subject: 'Código de Verificación - ' . config('app.name'),
            view: 'emails.notifications.verification-code',
            data: [
                'userName' => $user->nombre_completo,
                'expiresIn' => $expiresIn,
                'code' => $code
            ],
            others: '',
            attachment: '',
            delete_attachment_on_sent: false
        );

        // 4. Guardar temporalmente el ID del usuario en la sesión para el componente Livewire
        session(['two_factor_user_id' => $user->id, 'two_factor_remember' => $data['remember']]);

        RateLimiter::clear($throttleKey);

        return redirect()->route('auth.two-factor');
    }
}
