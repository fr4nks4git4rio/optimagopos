@extends('emails.layout')
@section('content')
    <h1 style="text-align: center">Hola {{ $userName }}</h1>
    <br>
    <p>Has iniciado sesión en **{{ config('app.name') }}**. Para completar tu acceso, ingresa el siguiente código:</p>
    <h1 class="text-center">
        {{ $code }}
    </h1>
    **Importante:** Este código expira en {{ $expiresIn }} minuto(s).

    Si no solicitaste este código, por favor ignora este mensaje.

    Saludos,
@endsection
