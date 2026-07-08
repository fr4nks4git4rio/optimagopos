<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SendEmailJob;
use App\Models\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        // 1. Validar que el correo exista en tu base de datos
        $request->validate(['email' => 'required|email|exists:tb_usuarios,email']);

        // 2. Buscar al usuario
        $user = User::where('email', $request->email)->first();

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
            return back()->with('status', 'Hemos enviado el enlace de recuperación a tu correo electrónico.');
        } catch (\Exception $e) {
            // En caso de que tu método de correo falle
            return back()->withErrors(['email' => 'No se pudo enviar el correo. Inténtalo de nuevo más tarde.']);
        }
    }
}
