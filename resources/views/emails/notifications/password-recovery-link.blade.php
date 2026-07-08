@extends('emails.layout')
@section('content')
    <h1 style="text-align: center">Hola {{ $userName }}</h1>
    <br>
    <p style="font-size: 16px; line-height: 1.6; color: #4b5563;">
        Recibimos una solicitud para restablecer la contraseña de tu cuenta asociada a este correo electrónico.
    </p>

    <table cellpadding="0" cellspacing="0" width="100%" style="margin: 30px 0; text-align: center;">
        <tr>
            <td>
                <a class="button button-blue" href="{{ $resetUrl }}" target="_blank">
                    Restablecer Contraseña
                </a>
            </td>
        </tr>
    </table>

    <p style="font-size: 14px; line-height: 1.6; color: #6b7280;">
        ⏱️ Este enlace de recuperación expirará en <strong>60 minutos</strong> por motivos de seguridad.
    </p>

    <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 30px 0;">

    <p style="font-size: 13px; line-height: 1.5; color: #9ca3af; margin-bottom: 0;">
        Si tú no solicitaste este cambio, puedes ignorar este correo de forma segura; tu contraseña seguirá siendo la misma.
    </p>
@endsection
