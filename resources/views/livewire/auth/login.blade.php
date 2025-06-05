@section('title', __('Login'))
@push('styles')
    <style>
        html {
            background-image: url(images/splash/splash002.jpg);
            background-attachment: fixed;
            background-repeat: no-repeat;
            background-size: cover;
        }

        body {
            background: transparent;
        }

        .logo {
            /*color: #ffffff !important;*/
            font-size: 20px;
            font-weight: 700;
            letter-spacing: .05em;
            line-height: 60px;
            text-transform: uppercase;
        }
    </style>
@endpush

<div class="d-grid col-lg-4 mx-auto mt-5">
    <div class="text-center mb-5">
        {{-- <a href="#" class="logo">PUERTA MAYA</a>

        <br> --}}
        <!-- <img class="bg-black p-2 b-radius-5 m-auto" src="{{config('app.url')}}/images/bretail.png" alt=""> -->
{{--        <h2 class="logo mt-2">BRetail</h2>--}}
    </div>
    <form class="card" wire:submit.prevent="login">
        {{--<h5 class="card-header bg-custom">--}}
        {{----}}
        {{--</h5>--}}
        <div class="card-body p-4">
            <div class="p-2">
                <x-input-group :label="__('Email')" icon="bi bi-person" type="text" model="email"/>
                <x-input-group :label="__('Password')" icon="bi bi-keyboard" type="password" model="password"/>

                <div class="d-flex justify-content-between">
                    {{--                    --}}

                    {{--@if(Route::has('password.forgot'))--}}
                    {{--<a href="{{ route('password.forgot') }}">{{ __('Forgot password?') }}</a>--}}
                    {{--@endif--}}
                </div>
                <div class="form-group text-center mt-4">
                    <div class="row">
                        <div class="col-4 mt-2">
                            <x-checkbox :label="'Recuérdame'" model="remember"/>
                        </div>
                        <div class="col-8">
                            <button class="btn btn-site-primary btn-lg w-50 float-end" type="submit">Entrar</button>
                        </div>
                    </div>
{{--                    <div class="col-12">--}}
{{--                        <a class="btn btn-lg btn-danger w-100 mt-2" href="#">--}}
{{--                            <i class="bi bi-google float-start"></i> Iniciar sesión G Suite--}}
{{--                        </a>--}}
{{--                    </div>--}}
                </div>
            </div>
        </div>
        {{--<div class="card-footer d-flex justify-content-end bg-custom">--}}
        {{--<button type="submit" class="btn btn-site-primary">{{ __($caption_login) }}</button>--}}
        {{--</div>--}}
    </form>
</div>
