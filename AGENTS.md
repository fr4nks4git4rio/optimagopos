# AGENTS.md — OptimaGoPos (Laravel 10 + Livewire 3 + Vite 4)

Stack: PHP ^8.1 (dev runs 8.2), Laravel 10, Livewire 3, Bootstrap 5 + jQuery + Select2, ApexCharts lazy-loaded. Shell here is Windows PowerShell 5.1 — chain with `; if ($?) { }`, never `&&`.

## Commands
- Verify Blade/Livewire edit: `php -l app\Livewire\Home.php; if ($?) { php artisan view:cache }` then `php artisan view:clear` when done (stale compiled views hide fixes).
- Full cache reset (Windows): `ClearCache.bat` — order matters: `cache:clear → route:clear → route:cache → config:cache → view:clear → view:cache → permission:cache-reset → queue:restart`.
- Tests: `php artisan test --filter=Name` (only `tests/Feature/ExampleTest.php` + `tests/Unit/ExampleTest.php` exist). `phpunit.xml` has sqlite lines **commented out** — tests expect MySQL (`DB_DATABASE=erp`), `CACHE/SESSION=array`.
- Lint/format: StyleCI `laravel` preset (`.styleci.yml`); PHP via `vendor/bin/pint --test` (run without `--test` to fix).
- Frontend: `npm run dev` (HMR) / `npm run build`. Entry inputs are `resources/sass/app.scss` + `resources/js/app.js` (`vite.config.js`).

## Architecture
- Entry: `routes/web.php` — domain-split: `config('app.facturacion_url')` (public auto-facturación), `config('app.api_url')` (POS ingest `parse-ticket-json`, middleware `gopos.security` + `throttle:gopos`), main app (`auth`, `set.locale`, `two-factor`, `user-with-active-subscription`). Role prefixes: `admin/` = `SuperAdmin|Accountant`, `cliente/` = `Admin|Manager` + per-route `permission:*`; billing routes additionally gated by `conFacturacion`.
- Globals in `helpers.php` (composer `files` autoload): `user()`, `get_owner()`, `sucursales_disponibles()`. Use them; don't re-query `auth()->user()` relationships inline.
- Single-server assumption: dashboard uses `CACHE_DRIVER=file` snapshots (`Home::dashboardCacheKey()` → `home|cliente|tab|seccion|fechas|sucursales|terminales|sufijo`). Do not switch to per-request statics or drop the key parts.

## Dashboard `App\Livewire\Home` — do not regress
- Two cache levels, partial snapshots only: `loadVitals()` (5s, key `vitals2`) ↔ `computeVitals()` counters; `loadData()` (60s, key `detalle2`) ↔ `computeData()` graphs/tops. Mapping lives in `camposVitals()`/`camposDetalle()` + `tomarFoto()`/`aplicarFoto()`. Never cache/restore whole `$resumenData`/etc. — that restores stale graphs under a new date key and counters/graphs desync.
- `updated()` reloads only on `tab, seccion, fecha_inicio, fecha_fin, sucursales, terminales`. `loadTerminales()` writes `terminalesDisponibles` (not in that list) precisely to avoid a double reload — keep it that way.
- `tab=boh` forces both dates to today; `commonWhere()` uses sargable `fecha_transaccion >= inicio 00:00:00 AND <= fin 23:59:59`, `foh` excludes `modo_entrenamiento=1`, always `whereIn` memoized `sucursalIds()/terminalIds()`.
- Polling (`home.blade.php` `loopVitals` 5s / `loopDetalle` 30s) skips when `document.hidden`, `busy*`, or `window.__homeFiltroEnVuelo`. The `Livewire.hook('commit')` counter treats commits **without** `calls` as filter changes; polls carry `loadVitals`/`loadData` calls. Keep the `try/catch` fallbacks.
- Alpine/ApexCharts chart state convention: `null` = not loaded (show loading), `[]`/`{}` = loaded-empty (show "sin datos"). Initializing a series to `[]` in PHP means an empty result never fires `$watch` and loading hangs. Each chart needs `actualizar(v)` with `if (v === null || v === undefined) return;` + `$watch` + immediate call.
- Never put `"` (e.g. `"sin datos"`) inside an `x-data="..."` attribute comment — it closes the attribute and kills the first chart. ApexCharts loads only via `window.loadApexCharts()` (`resources/js/app.js`); `vite.config.js` `manualChunks.vendor` intentionally excludes it.
- Select2: `hydrate()`/`updated()` dispatch `reApplySelect2`; autocomplete routes are `throttle:120,1` with debounce — one request per keystroke is expected, don't "fix" by removing throttle.
