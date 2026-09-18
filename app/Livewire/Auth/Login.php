<?php

namespace App\Livewire\Auth;

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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Component;

class Login extends Component
{
    public $email;
    public $password;
    public $remember;
    public $lang;
    public $langs = ['es' => 'ES', 'en' => 'EN', 'fr' => 'FR'];

    public function mount()
    {
        $this->lang = app()->getLocale();
    }

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

    public function updated($field, $value)
    {
        if ($field == 'lang')
            app()->setLocale($value);
    }

    public function login()
    {
        $data = $this->validate(
            $this->rules(),
            // $this->messages()
        );

        $throttleKey = Str::lower($this->email) . '|' . request()->ip();

        // RateLimiter es best-effort: si el store de cache falla (p.ej.
        // storage/framework/cache sin permisos o tras un cache:clear),
        // se degrada a "sin limite" en lugar de romper el login con un 500.
        $throttled = $this->rateLimiterSafe(
            fn() => RateLimiter::tooManyAttempts($throttleKey, 5),
            'verificacion de intentos',
            $throttleKey
        ) ?? false;

        if ($throttled) {
            $this->addError('email', __('auth.throttle', [
                'seconds' => $this->rateLimiterSafe(
                    fn() => RateLimiter::availableIn($throttleKey),
                    'tiempo restante',
                    $throttleKey
                ) ?? 0,
            ]));

            return;
        }

        if (! auth()->validate(Arr::only($data, ['email', 'password']))) {
            $this->rateLimiterSafe(fn() => RateLimiter::hit($throttleKey), 'registro de intento', $throttleKey);

            $this->addError('email', __('auth.failed'));
            return;
        }


        $user = User::where('email', $this->email)->first();

        if ($user->hasAnyRole(['Admin', 'Manager']) && $user->suscripciones_activas()->count() == 0) {
            $this->addError('email', __('auth.subscription_failed'));
            return;
        }

        // 2. Comprobar si el dispositivo es de confianza
        $cookieName = 'device_trusted_' . $user->id;
        if (Cookie::has($cookieName)) {
            // Dispositivo de confianza: Loguear directo
            auth()->login($user, $data['remember']);
            $this->rateLimiterSafe(fn() => RateLimiter::clear($throttleKey), 'limpieza de intentos', $throttleKey);

            activity(__('site.auth.log_user_logged'))
                ->on($user)
                ->event('login')
                ->withProperties(Arr::except(
                    $user->toArray(),
                    ['password', 'created_at', 'updated_at', 'deleted_at']
                ))
                ->log(__('site.auth.log_user_logged_detail', ['email' => $user->email]));

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
            subject: __('site.auth.email_2fa_subject', ['app_name' => config('app.name')]),
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

        $this->rateLimiterSafe(fn() => RateLimiter::clear($throttleKey), 'limpieza de intentos', $throttleKey);

        return redirect()->route('auth.two-factor');
    }

    private function rateLimiterSafe(callable $fn, string $context, string $throttleKey): mixed
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            Log::warning(
                "Rate limiter no operativo durante {$context} en login: " . $e->getMessage(),
                ['key' => $throttleKey]
            );

            return null;
        }
    }
}
