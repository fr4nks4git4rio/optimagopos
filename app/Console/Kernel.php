<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('app:generar-fact-periodicas-suscripciones')->dailyAt('05:00');
        // Limpieza de PDFs temporales de reportes (storage/app/pdfs) mayores a 24h.
        $schedule->call(function () {
            $files = \Illuminate\Support\Facades\Storage::files('pdfs');
            $limite = now()->subDay()->timestamp;
            foreach ($files as $file) {
                if (\Illuminate\Support\Facades\Storage::lastModified($file) < $limite) {
                    \Illuminate\Support\Facades\Storage::delete($file);
                }
            }
        })->dailyAt('04:00')->name('limpieza-pdfs-temporales');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
