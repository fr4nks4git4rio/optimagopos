<?php

use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\CfdiController;
use App\Http\Controllers\ClaveProdServController;
use App\Http\Controllers\ClaveUnidadController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\FormaPagoController;
use App\Http\Controllers\GmailOAuthController;
use App\Http\Controllers\LocalidadController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\MunicipioController;
use App\Http\Controllers\ObjetoImpuestoController;
use App\Http\Controllers\SerieController;
use App\Http\Controllers\SoapController;
use App\Http\Controllers\TipoComprobanteController;
use App\Livewire\Home;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Passwords\ForgotPassword;
use App\Livewire\Auth\Passwords\ResetPassword;
use App\Livewire\AutoFacturacion;
use App\Livewire\Facturas\CabeceraFactura;
use App\Livewire\Reportes\HistoricoTicketsVk;
use App\Livewire\Trazas\Index as IndexTrazas;
use App\Livewire\Roles\IndexSystem as IndexSystemRoles;
use App\Livewire\Roles\Index as IndexRoles;
use App\Livewire\Usuarios\Index as IndexUsuarios;
use App\Livewire\GestionConfiguracionesComponent as IndexConfiguraciones;
use App\Livewire\Modulos\Index as IndexModulos;
use App\Livewire\Paquetes\Index as IndexPaquetes;
use App\Livewire\Clientes\Index as IndexClients;
use App\Livewire\Comensales\Index as IndexComensales;
use App\Livewire\Sucursales\Index as IndexSucursales;
use App\Livewire\Terminales\Index as IndexTerminales;
use App\Livewire\Cuarentena\Index as IndexCuarentena;
use App\Livewire\Suscripciones\Index as IndexSuscripciones;
use App\Livewire\Suscripciones\GestionSuscripciones;
use App\Livewire\Facturas\IndexAlmacen as IndexAlmacenFacturas;
use App\Livewire\Facturas\IndexPreFacturas;
use App\Livewire\Facturas\Save as SavePreFacturas;
use App\Livewire\FacturasSistema\IndexAlmacen as IndexAlmacenFacturasSistema;
use App\Livewire\CuentasCobrar\Index as IndexCuentasCobrarSistema;
use App\Livewire\FacturasSistema\IndexPreFacturas as IndexPreFacturasSistema;
use App\Livewire\FacturasSistema\Save as SavePreFacturasSistema;
use App\Livewire\FacturasSistema\SaveComplemento as SaveComplementoSistema;
use App\Livewire\FacturasSistema\SaveNotaCredito as SaveNotaCreditoSistema;
use App\Livewire\FacturasSistema\CabeceraFactura as CabeceraFacturaSistema;
use App\Livewire\Reportes\HistoricoOperaciones\Index as IndexHistoricoOperaciones;
use App\Livewire\Reportes\HistoricoOperaciones\IndexTesting as IndexHistoricoOperacionesTesting;
use App\Livewire\Reportes\ProductosMasVendidos;
use App\Livewire\Reportes\Logs;
use App\Livewire\Reportes\Ingresos as ReporteIngresos;
use App\Livewire\TimbrarAutoFactura;
use App\Livewire\Auth\TwoFactorChallenge;
use App\Livewire\Reportes\ArticulosVendidos;
use App\Livewire\Reportes\VentasDiarias;
use App\Livewire\Reportes\VentasOperador;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

//use App\Http\Livewire\Cotizador\Catalogos\Productos\SaveV2 as SaveProductos;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Auth::routes();
Route::domain(config('app.facturacion_url'))->group(function () {
    Route::get('/', function () {
        return redirect()->route('auto-facturacion');
    });
    Route::get('/login', function () {
        return redirect()->route('auto-facturacion');
    });
    Route::get('/auto-facturacion', AutoFacturacion::class)->name('auto-facturacion');
    Route::get('/timbrar-auto-factura/{id}', TimbrarAutoFactura::class)->name('timbrar-auto-factura');
});

Route::domain(config('app.api_url'))->group(function () {
    Route::post('/', [HomeController::class, 'parseTicketJson']);
    Route::post('/parse-ticket-json', [HomeController::class, 'parseTicketJson']);
    Route::post('/parse-ticket-vk-json', [HomeController::class, 'parseTicketVKJson']);
});

Route::get('/load-estados', [EstadoController::class, 'loadEstados'])->name('estados.load-estados');
Route::get('/load-municipios', [MunicipioController::class, 'loadMunicipios'])->name('municipios.load-municipios');
Route::get('/load-localidades', [LocalidadController::class, 'loadLocalidades'])->name('localidades.load-localidades');

Route::middleware(['auth', 'set.locale', 'two-factor', 'user-with-active-subscription'])->group(function () {

    Route::get('/home', Home::class)->name('home');

    Route::get('/load-clientes', [ClienteController::class, 'loadClientes'])->name('clientes.load-clientes');
    Route::get('/load-comensales', [ClienteController::class, 'loadComensales'])->name('clientes.load-comensales');
    Route::get('/load-cfdis', [CfdiController::class, 'loadCfdis'])->name('cfdis.load-cfdis');
    Route::get('/load-claves-prod-servs', [ClaveProdServController::class, 'loadClavesProdServs'])->name('claves-prod-servs.load-claves-prod-servs');
    Route::get('/load-claves-unidades', [ClaveUnidadController::class, 'loadClavesUnidades'])->name('claves-unidades.load-claves-unidades');
    Route::get('/load-formas-pagos', [FormaPagoController::class, 'loadFormasPagos'])->name('formas-pagos.load-formas-pagos');
    Route::get('/load-metodos-pagos', [MetodoPagoController::class, 'loadMetodosPagos'])->name('metodos-pagos.load-metodos-pagos');
    Route::get('/load-objetos-impuestos', [ObjetoImpuestoController::class, 'loadObjetosImpuestos'])->name('objetos-impuestos.load-objetos-impuestos');
    Route::get('/load-tipos-comprobantes', [TipoComprobanteController::class, 'loadTiposComprobantes'])->name('tipos-comprobantes.load-tipos-comprobantes');
    Route::get('/load-series', [SerieController::class, 'loadSeries'])->name('series.load-series');

    Route::middleware(['role:SuperAdmin|Accountant'])->prefix('admin')->group(function () {

        Route::get('/modulos', IndexModulos::class)->name('admin.modulos.index')->middleware('role:SuperAdmin');
        Route::get('/paquetes', IndexPaquetes::class)->name('admin.paquetes.index')->middleware('role:SuperAdmin');
        Route::get('/roles', IndexSystemRoles::class)->name('admin.roles.index')->middleware('role:SuperAdmin');
        Route::get('/usuarios', IndexUsuarios::class)->name('admin.usuarios.index')->middleware('role:SuperAdmin');
        Route::get('/configuraciones', IndexConfiguraciones::class)->name('admin.configuraciones.index')->middleware('role:SuperAdmin');
        Route::get('/cuarentena', IndexCuarentena::class)->name('admin.cuarentena.index')->middleware('role:SuperAdmin');
        Route::get('/trazas', IndexTrazas::class)->name('admin.trazas.index')->middleware('role:SuperAdmin');

        Route::get('/clientes', IndexClients::class)->name('admin.clientes.index');
        Route::get('/sucursales', IndexSucursales::class)->name('admin.sucursales.index');
        Route::get('/terminales', IndexTerminales::class)->name('admin.terminales.index')->middleware('role:SuperAdmin');
        Route::get('/suscripciones', IndexSuscripciones::class)->name('admin.suscripciones.index');
        Route::get('/suscripciones/gestion-suscripcion/{suscripcionId?}', GestionSuscripciones::class)->name('admin.suscripciones.save')->middleware('role:SuperAdmin');

        Route::get('/pre-facturas/save/{id?}', SavePreFacturasSistema::class)->name('admin.pre-facturas.save');
        Route::get('/complementos/save/{id?}', SaveComplementoSistema::class)->name('admin.complementos.save');
        Route::get('/notas-credito/save/{id?}', SaveNotaCreditoSistema::class)->name('admin.notas-credito.save');
        Route::get('/pre-facturas', IndexPreFacturasSistema::class)->name('admin.pre-facturas.index');
        Route::get('/almacen-facturas', IndexAlmacenFacturasSistema::class)->name('admin.almacen-facturas.index');
        Route::get('/cuentas-cobrar', IndexCuentasCobrarSistema::class)->name('admin.cuentas-cobrar.index');
        Route::get('/cabecera-factura', CabeceraFacturaSistema::class)->name('admin.cabecera-factura');
        Route::get('/obtener-timbres-disponibles/{rfc}', [SoapController::class, 'obtenerTimbresDisponibles']);

        Route::get('/load-cuentas-cobrar', [FacturaController::class, 'loadCuentasCobrar'])->name('admin.cuentas-cobrar.load');
        Route::get('/print-listado-cuentas-cobrar', [FacturaController::class, 'imprimirListadoCuentasCobrar'])->name('admin.cuentas-cobrar.print-listado');

        Route::prefix('reportes')->group(function () {
            Route::get('/historico-tickets-vk', HistoricoTicketsVk::class)->name('admin.reportes.historico-tickets-vk')->middleware('permission:reportsVKTicketHistory-viewAny');
            Route::get('/ventas-diarias', VentasDiarias::class)->name('admin.reportes.ventas-diarias')->middleware('permission:reportsDailySales-viewAny');
            Route::get('/articulos-vendidos', ArticulosVendidos::class)->name('admin.reportes.articulos-vendidos')->middleware('permission:reportsArticlesSold-viewAny');
            Route::get('/ventas-operador', VentasOperador::class)->name('admin.reportes.ventas-operador')->middleware('permission:reportsSalesByOperator-viewAny');
            Route::get('/productos-mas-vendidos', ProductosMasVendidos::class)->name('admin.reportes.productos-mas-vendidos')->middleware('permission:reportsBestSellingProducts-viewAny');
            Route::get('/historico-operaciones', IndexHistoricoOperaciones::class)->name('admin.reportes.historico-operaciones')->middleware('permission:reportsOperationsHistory-viewAny');
            Route::get('/testing-historico-operaciones', IndexHistoricoOperacionesTesting::class)->name('admin.reportes.testing-historico-operaciones')->middleware('permission:reportsTestingOperationsHistory-viewAny');
            Route::get('/ingresos', ReporteIngresos::class)->name('admin.reportes.ingresos');
            Route::get('/logs', Logs::class)->name('admin.reportes.logs');
        });
    });

    Route::middleware(['role:Admin|Manager'])->prefix('cliente')->group(function () {
        Route::get('/roles', IndexRoles::class)->name('cliente.roles.index')->middleware('permission:roles-viewAny');
        Route::get('/usuarios', IndexUsuarios::class)->name('cliente.usuarios.index')->middleware('permission:users-viewAny');
        Route::get('/trazas', IndexTrazas::class)->name('cliente.trazas.index')->middleware('permission:logs-viewAny');
        // Route::get('/comensales', IndexComensales::class)->name('cliente.comensales.index');
        Route::get('/sucursales', IndexSucursales::class)->name('cliente.sucursales.index')->middleware('permission:branches-viewAny');
        Route::get('/terminales', IndexTerminales::class)->name('cliente.terminales.index')->middleware('permission:terminals-viewAny');

        Route::middleware('conFacturacion')->group(function () {
            Route::get('/pre-facturas/save/{id?}', SavePreFacturas::class)->name('cliente.pre-facturas.save')->middleware('permission:invoices-create|invoices-update');
            Route::get('/pre-facturas', IndexPreFacturas::class)->name('cliente.pre-facturas.index')->middleware('permission:invoices-viewAny');

            Route::get('/almacen-facturas', IndexAlmacenFacturas::class)->name('cliente.almacen-facturas.index')->middleware('permission:invoices-viewAny');

            Route::get('/cabecera-factura', CabeceraFactura::class)->name('cliente.cabecera-factura')->middleware('permission:invoiceHeader-view');
            Route::get('/obtener-timbres-disponibles/{rfc}', [SoapController::class, 'obtenerTimbresDisponibles']);
        });

        Route::prefix('reportes')->group(function () {
            Route::get('/historico-tickets-vk', HistoricoTicketsVk::class)->name('cliente.reportes.historico-tickets-vk')->middleware('permission:reportsVKTicketHistory-viewAny');
            Route::get('/ventas-diarias', VentasDiarias::class)->name('cliente.reportes.ventas-diarias')->middleware('permission:reportsDailySales-viewAny');
            Route::get('/articulos-vendidos', ArticulosVendidos::class)->name('cliente.reportes.articulos-vendidos')->middleware('permission:reportsArticlesSold-viewAny');
            Route::get('/ventas-operador', VentasOperador::class)->name('cliente.reportes.ventas-operador')->middleware('permission:reportsSalesByOperator-viewAny');
            Route::get('/productos-mas-vendidos', ProductosMasVendidos::class)->name('cliente.reportes.productos-mas-vendidos')->middleware('permission:reportsBestSellingProducts-viewAny');
            Route::get('/historico-operaciones', IndexHistoricoOperaciones::class)->name('cliente.reportes.historico-operaciones')->middleware('permission:reportsOperationsHistory-viewAny');
            Route::get('/testing-historico-operaciones', IndexHistoricoOperacionesTesting::class)->name('cliente.reportes.testing-historico-operaciones')->middleware('permission:reportsTestingOperationsHistory-viewAny');
            Route::get('/logs', Logs::class)->name('cliente.reportes.logs')->middleware('permission:reportsDataReceived-viewAny');
        });
    });
});

Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('auth.provider-redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('auth.provider-callback');

Route::get('/oauth2/redirect', [GmailOAuthController::class, 'redirect']);
Route::get('/oauth2/callback', [GmailOAuthController::class, 'callback']);

Route::middleware(['guest'])->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/', function () {
        return redirect()->route('login');
    });
    Route::get('/two-factor', TwoFactorChallenge::class)->name('auth.two-factor');


    Route::get('forgot-password', ForgotPassword::class)->name('password.forgot');
    // Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('reset-password/{token}', ResetPassword::class)->name('password.reset');
    // Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});
