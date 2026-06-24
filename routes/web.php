<?php

use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\CfdiController;
use App\Http\Controllers\ClaveProdServController;
use App\Http\Controllers\ClaveUnidadController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\FormaPagoController;
use App\Http\Controllers\LocalidadController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\MunicipioController;
use App\Http\Controllers\ObjetoImpuestoController;
use App\Http\Controllers\SerieController;
use App\Http\Controllers\SoapController;
use App\Http\Controllers\TipoComprobanteController;
use App\Http\Livewire\Facturas\Save as SavePreFacturas;
use App\Http\Livewire\Home;
use App\Http\Livewire\Auth\Login;
use App\Http\Livewire\AutoFacturacion;
use App\Http\Livewire\CabeceraFactura;
use App\Http\Livewire\Trazas\Index as IndexTrazas;
use App\Http\Livewire\Usuarios\Index as IndexUsuarios;
use App\Http\Livewire\Clientes\Index as IndexClients;
use App\Http\Livewire\Comensales\Index as IndexComensales;
use App\Http\Livewire\Sucursales\Index as IndexSucursales;
use App\Http\Livewire\Terminales\Index as IndexTerminales;
use App\Http\Livewire\Facturas\IndexAlmacen as IndexAlmacenFacturas;
use App\Http\Livewire\Facturas\IndexPreFacturas;
use App\Http\Livewire\Reportes\Tickets\Index as IndexReportesTickets;
use App\Http\Livewire\Reportes\VentasPeriodo;
use App\Http\Livewire\Reportes\ProductosMasVendidos;
use App\Http\Livewire\Reportes\Logs;
use App\Http\Livewire\TimbrarAutoFactura;
use App\Models\Cliente;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;


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

//Auth::routes();

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
});

Route::get('/load-estados', [EstadoController::class, 'loadEstados'])->name('estados.load-estados');
Route::get('/load-municipios', [MunicipioController::class, 'loadMunicipios'])->name('municipios.load-municipios');
Route::get('/load-localidades', [LocalidadController::class, 'loadLocalidades'])->name('localidades.load-localidades');

Route::middleware(['auth', 'set.locale'])->group(function () {

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

    Route::middleware(['hasRole:1'])->prefix('admin')->group(function () {
        Route::get('/usuarios', IndexUsuarios::class)->name('admin.usuarios.index');
        Route::get('/trazas', IndexTrazas::class)->name('admin.trazas.index');

        Route::get('/clientes', IndexClients::class)->name('admin.clientes.index');
        Route::get('/sucursales', IndexSucursales::class)->name('admin.sucursales.index');
        Route::get('/terminales', IndexTerminales::class)->name('admin.terminales.index');

        Route::prefix('reportes')->group(function () {
            Route::get('/logs', Logs::class)->name('admin.reportes.logs');
        });
    });

    Route::middleware(['hasRole:2'])->prefix('cliente')->group(function () {
        Route::get('/usuarios', IndexUsuarios::class)->name('cliente.usuarios.index');
        Route::get('/trazas', IndexTrazas::class)->name('cliente.trazas.index');
        Route::get('/comensales', IndexComensales::class)->name('cliente.comensales.index');
        Route::get('/sucursales', IndexSucursales::class)->name('cliente.sucursales.index');
        Route::get('/terminales', IndexTerminales::class)->name('cliente.terminales.index');

        Route::middleware('conFacturacion')->group(function () {
            Route::get('/pre-facturas/save/{id?}', SavePreFacturas::class)->name('cliente.pre-facturas.save');
            Route::get('/pre-facturas', IndexPreFacturas::class)->name('cliente.pre-facturas.index');

            Route::get('/almacen-facturas', IndexAlmacenFacturas::class)->name('cliente.almacen-facturas.index');

            Route::get('/cabecera-factura', CabeceraFactura::class)->name('cliente.cabecera-factura');
            Route::get('/obtener-timbres-disponibles/{rfc}', [SoapController::class, 'obtenerTimbresDisponibles']);
        });

        Route::prefix('reportes')->group(function () {

            Route::get('/ventas-periodo', VentasPeriodo::class)->name('cliente.reportes.ventas-periodo');
            Route::get('/productos-mas-vendidos', ProductosMasVendidos::class)->name('cliente.reportes.productos-mas-vendidos');
            Route::get('/tickets', IndexReportesTickets::class)->name('cliente.reportes.tickets');
            Route::get('/logs', Logs::class)->name('cliente.reportes.logs');
        });
    });
});

Route::middleware(['guest'])->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/', function () {
        return redirect()->route('login');
    });
});
