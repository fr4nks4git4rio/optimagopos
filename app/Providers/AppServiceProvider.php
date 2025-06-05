<?php

namespace App\Providers;

use App\Models\Cliente;
use App\Models\Sucursal;
use App\Models\Terminal;
use App\Models\User;
use App\Observers\ClienteObserver;
use App\Observers\SucursalObserver;
use App\Observers\TerminalObserver;
use App\Observers\UserObserver;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

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

        setlocale(LC_ALL, 'es_MX', 'es', 'ES', 'es_MX.utf8');
        Carbon::setLocale('es_MX.utf8');
    }
}
