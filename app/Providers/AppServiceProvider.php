<?php

namespace App\Providers;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Modulo;
use App\Models\Paquete;
use App\Models\Sucursal;
use App\Models\Terminal;
use App\Models\User;
use App\Observers\ClienteObserver;
use App\Observers\FacturaObserver;
use App\Observers\ModuloObserver;
use App\Observers\PaqueteObserver;
use App\Observers\SucursalObserver;
use App\Observers\TerminalObserver;
use App\Observers\UserObserver;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\GoogleProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
        Cliente::observe(ClienteObserver::class);
        Sucursal::observe(SucursalObserver::class);
        Terminal::observe(TerminalObserver::class);
        Factura::observe(FacturaObserver::class);
        Paquete::observe(PaqueteObserver::class);
        Modulo::observe(ModuloObserver::class);

        Password::defaults(function () {
            return Password::min(8)
                ->max(64)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised();
        });

        Socialite::extend('google-login', function ($app) {
            $config = $app['config']['services.google-login'];

            return Socialite::buildProvider(
                GoogleProvider::class,
                $config
            );
        });

        setlocale(LC_ALL, 'es_MX', 'es', 'ES', 'es_MX.utf8');
        Carbon::setLocale('es_MX.utf8');
    }
}
