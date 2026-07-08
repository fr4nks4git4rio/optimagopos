<?php

namespace App\Http\Livewire\Auth\Passwords;

use App\Jobs\SendEmailJob;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Livewire\Component;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Password;

class ForgotPassword extends Component
{
    public $email;
    protected $rules = [
        'email' => 'required|email|exists:tb_usuarios,email',
    ];

    // protected $messages = [
    //     'code.required' => 'Campo obligatorio.',
    //     'code.numeric' => 'Campo numérico.',
    //     'code.digits' => 'Entre 6 dígitos.',
    // ];

    public function sendResetLinkEmail()
    {
        // 1. Validar que el correo exista en tu base de datos
        $this->validate();

        // 2. Buscar al usuario
        $user = User::where('email', $this->email)->first();

        // 3. Crear el token de recuperación usando el Broker nativo de Laravel
        // Esto asegura que el token se guarde en la tabla 'password_reset_tokens' con su respectiva expiración
        $token = Password::getRepository()->create($user);

        // 4. Construir la URL que se enviará en el correo
        // Esta URL debe apuntar a tu ruta 'password.reset'
        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->email
        ]);

        try {
            // 5. LLAMAR A TU MÉTODO PROPIO DE ENVÍO DE CORREOS
            // Aquí pasas el $user, la $resetUrl o el $token según lo requiera tu función
            SendEmailJob::dispatch(
                recipients: $user->email,
                from_email: '',
                from_name: '',
                subject: 'Recuperación de Contraseña - ' . config('app.name'),
                view: 'emails.notifications.password-recovery-link',
                data: [
                    'userName' => $user->nombre_completo,
                    'resetUrl' => $resetUrl
                ],
                others: '',
                attachment: '',
                delete_attachment_on_sent: false
            );

            // 6. Retornar respuesta de éxito
            session()->flash('message', 'Se ha enviado un nuevo código a tu correo electrónico.');
        } catch (\Exception $e) {
            // En caso de que tu método de correo falle
            $this->addError('email', 'No se pudo enviar el correo. Inténtalo de nuevo más tarde.');
        }
    }

    public function render()
    {
        return view('livewire.auth.passwords.email');
    }
}
