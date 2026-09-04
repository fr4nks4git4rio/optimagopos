php artisan cache:clear
php artisan route:clear 
php artisan route:cache 
php artisan config:cache
php artisan view:clear
php artisan view:cache
php artisan permission:cache-reset
php artisan queue:restart
REM === Checklist deploy produccion (single server, sin Redis) ===
REM 1. .env: APP_ENV=production, APP_DEBUG=false
REM 2. Worker colas supervisado (Supervisor/systemd): php artisan queue:work --sleep=3 --tries=3
REM    (este .bat solo pide reinicio graceful via queue:restart)
REM 3. Cron del scheduler (limpieza PDFs storage/app/pdfs + facturas periodicas):
REM    * * * * * cd /ruta/proyecto && php artisan schedule:run >> /dev/null 2>&1
REM 4. OPcache php.ini prod: opcache.enable=1, opcache.validate_timestamps=0 + reload PHP-FPM tras deploy