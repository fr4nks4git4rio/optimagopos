@extends('emails.layout')
@section('content')
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed; background-color: #f4f6f9;">
        <tr>
            <td align="center" style="padding: 40px 10px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%"
                    style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    <tr>
                        <td align="center" style="background-color: #212529; padding: 30px 20px;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 900; letter-spacing: 1px;">
                                {{ config('app.name') }}</h1>
                            <p
                                style="color: #0d6efd; margin: 5px 0 0 0; font-size: 14px; font-weight: bold; text-transform: uppercase;">
                                Suscripción Activada</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 1.6;">
                                Estimado(a) equipo de
                                <strong>{{ $cliente }}</strong>,
                            </p>
                            <p style="margin: 0 0 30px 0; font-size: 15px; line-height: 1.6; color: #555555;">
                                Nos complace notificarte que la infraestructura y aprovisionamiento de tu entorno digital
                                han sido validados con éxito. Tu cuenta ya se encuentra registrada y en estado <span
                                    style="background-color: #d1e7dd; color: #0f5132; padding: 3px 8px; font-size: 12px; font-weight: bold; border-radius: 4px;">ACTIVA</span>.
                            </p>

                            <table border="0" cellpadding="0" cellspacing="0" width="100%"
                                style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 30px; border-left: 4px solid #0d6efd;">
                                <tr>
                                    <td style="font-size: 14px; line-height: 1.8;">
                                        <strong>RFC de Cliente:</strong> {{ $rfc }}<br>
                                        <strong>Fecha de Operaciones:</strong>
                                        {{ \Carbon\Carbon::parse($fecha_inicio_operaciones)->format('d/m/Y') }}<br>
                                        <strong>Próxima Fecha de Cobro:</strong>
                                        {{ $fecha_inicio_pagos ? \Carbon\Carbon::parse($fecha_inicio_pagos)->format('d/m/Y') : '— / — / —' }}<br>
                                        <strong>Ciclo de Facturación:</strong> {{ $periodicidad_pagos }}<br>
                                        <strong>Monto Recurrente Neto:</strong> <span
                                            style="color: #0d6efd; font-weight: bold;">${{ number_format($precio_total, 2) }}
                                            {{ $moneda }}</span>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 1.6; color: #555555;">
                                Hemos adjuntado a este correo tu documento de **Confirmación Contractual** en PDF para tus
                                registros internos. En él podrás verificar el desglose completo de los módulos adquiridos y
                                las capacidades máximas de tus sucursales y terminales.
                            </p>

                            <p style="margin: 0 0 30px 0; font-size: 15px; line-height: 1.6; color: #555555;">
                                En los próximos minutos recibirás un correo adicional con tus accesos de Administrador
                                Maestro para comenzar a operar.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center"
                            style="background-color: #f1f3f5; padding: 20px; font-size: 12px; color: #6c757d; border-top: 1px solid #dee2e6;">
                            Este es un correo automático generado por el sistema de asignación. Por favor no lo
                            respondas.<br>
                            Si tienes dudas, contacta a soporte corporativo.<br>
                            <strong>© {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</strong>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
@endsection
