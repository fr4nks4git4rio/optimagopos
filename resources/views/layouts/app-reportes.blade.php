<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

{{--    <link rel="icon" type="image/png" sizes="32x32" href="{{asset("images/favicon-32x32.png")}}">--}}
{{--    <link rel="icon" type="image/png" sizes="16x16" href="{{asset("images/favicon-16x16.png")}}">--}}

    <title>{{ env('APP_NAME','APPLICATION NAME') }}</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!--STYLESHEET-->
    <!--=================================================-->

    <!--Bootstrap Stylesheet [ REQUIRED ]-->
{{--    <link href="{{ asset('css/bootstrap.min.css') }} " rel="stylesheet">--}}
{{--    <link href="{{ asset('css/icons.css') }} " rel="stylesheet">--}}
{{--    <link href="{{ asset('css/style.css') }} " rel="stylesheet">--}}

    @yield('styles')

</head>
<body style="background-color:white;">
{{-- <body class="fixed-left" > --}}
{{-- <div class="container-fluid"  id="page-content"> --}}

@yield('content')

{{-- </div> --}}

</body>
</html>
