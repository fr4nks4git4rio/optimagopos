<?php

namespace App\Http\Livewire\Auth;

use App\Jobs\SendEmailJob;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Livewire\Component;
use Illuminate\Support\Facades\Cookie;

class TwoFactorChallenge extends Component
{
    public $code;
    public $rememberDevice = false;

    protected $rules = [
        'code' => 'required|numeric|digits:6',
    ];

    protected $messages = [
        'code.required' => 'Campo obligatorio.',
        'code.numeric' => 'Campo numérico.',
        'code.digits' => 'Entre 6 dígitos.',
    ];

    public function mount()
    {
        if (!session()->has('two_factor_user_id')) {
            return redirect()->route('login');
        }
    }

    public function verify()
    {
        $this->validate();

        $user = User::find(session('two_factor_user_id'));

        if (!$user || $user->two_factor_code !== $this->code || now()->isAfter($user->two_factor_expires_at)) {
            $this->addError('code', 'El código es inválido o ha expirado.');
            return;
        }

        // Limpiar código usado
        $user->update([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ]);

        // Si marcó dispositivo de confianza, creamos una cookie por 30 días
        if ($this->rememberDevice) {
            Cookie::queue('device_trusted_' . $user->id, true, 60 * 24 * 30); // 30 días
        }

        // Iniciar sesión formalmente
        auth()->login($user, session('two_factor_remember', false));

        // Limpiar sesión temporal
        session()->forget(['two_factor_user_id', 'two_factor_remember']);

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Acción para reenviar el código de verificación
     */
    public function resendCode()
    {
        $user = User::find(session('two_factor_user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        // Generar nuevo código y extender expiración
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

        // Enviar un mensaje de éxito que se mostrará temporalmente en la vista
        session()->flash('message', 'Se ha enviado un nuevo código a tu correo electrónico.');
    }

    /**
     * Acción para desconectar / cancelar el proceso y volver al Login
     */
    public function logout()
    {
        // Limpiamos los datos temporales del 2FA en la sesión
        session()->forget(['two_factor_user_id', 'two_factor_remember']);

        // Por seguridad, nos aseguramos de que no haya ninguna sesión a medias
        auth()->logout();

        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.auth.two-factor-challenge');
    }
}
