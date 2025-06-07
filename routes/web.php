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
use App\Http\Livewire\TimbrarAutoFactura;
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
    Route::get('/', AutoFacturacion::class)->name('auto-facturacion');
    Route::get('/login', function(){
        return redirect()->route('auto-facturacion');
    });
    Route::get('/timbrar-auto-factura/{id}', TimbrarAutoFactura::class)->name('timbrar-auto-factura');
});

Route::domain(config('app.api_url'))->group(function () {
    Route::post('/', [HomeController::class, 'parseJson'])->name('auto-facturacion');
});

Route::get('/insert_admin', function () {
    DB::table('tb_usuarios')
        ->insert([
            'email' => 'admin@admin.com',
            'nombre' => 'Super Admin',
            'apellidos' => 'Administrador',
            'password' => Hash::make('Admin123**'),
            'rol_id' => 1
        ]);

    echo "ECHO!!!!";
});

Route::post('/', []);

Route::get('/load-estados', [EstadoController::class, 'loadEstados'])->name('estados.load-estados');
Route::get('/load-municipios', [MunicipioController::class, 'loadMunicipios'])->name('municipios.load-municipios');
Route::get('/load-localidades', [LocalidadController::class, 'loadLocalidades'])->name('localidades.load-localidades');

Route::middleware(['auth'])->group(function () {

    Route::get('/home', Home::class)->name('home');

    Route::get('/load-clientes', [ClienteController::class, 'loadClientes'])->name('clientes.load-clientes');
    Route::get('/load-cfdis', [CfdiController::class, 'loadCfdis'])->name('cfdis.load-cfdis');
    Route::get('/load-claves-prod-servs', [ClaveProdServController::class, 'loadClavesProdServs'])->name('claves-prod-servs.load-claves-prod-servs');
    Route::get('/load-claves-unidades', [ClaveUnidadController::class, 'loadClavesUnidades'])->name('claves-unidades.load-claves-unidades');
    Route::get('/load-formas-pagos', [FormaPagoController::class, 'loadFormasPagos'])->name('formas-pagos.load-formas-pagos');
    Route::get('/load-metodos-pagos', [MetodoPagoController::class, 'loadMetodosPagos'])->name('metodos-pagos.load-metodos-pagos');
    Route::get('/load-objetos-impuestos', [ObjetoImpuestoController::class, 'loadObjetosImpuestos'])->name('objetos-impuestos.load-objetos-impuestos');
    Route::get('/load-tipos-comprobantes', [TipoComprobanteController::class, 'loadTiposComprobantes'])->name('tipos-comprobantes.load-tipos-comprobantes');
    Route::get('/load-series', [SerieController::class, 'loadSeries'])->name('series.load-series');

    Route::middleware(['hasRole:1|2'])->group(function () {
        Route::get('/usuarios', IndexUsuarios::class)->name('usuarios.index');
        Route::get('/trazas', IndexTrazas::class)->name('trazas.index');

        Route::get('/clientes', IndexClients::class)->name('clientes.index')->middleware('hasRole:1');
        Route::get('/comensales', IndexComensales::class)->name('comensales.index')->middleware('hasRole:2');
        Route::get('/sucursales', IndexSucursales::class)->name('sucursales.index');
        Route::get('/terminales', IndexTerminales::class)->name('terminales.index');

        Route::get('/almacen-facturas', IndexAlmacenFacturas::class)->name('almacen-facturas.index')->middleware('hasRole:2');

        Route::get('/cabecera-factura', CabeceraFactura::class)->name('cabecera-factura');
        Route::get('/obtener-timbres-disponibles/{rfc}', [SoapController::class, 'obtenerTimbresDisponibles']);
    });
});

Route::middleware(['guest'])->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/', function () {
        return redirect()->route('login');
    });
});

Route::get('/parse_json', function () {
    $json1 = json_decode("{\"ClerkId\":\"4\",\"ClerkName\":\"Tecnico \",\"Company\":\"Optima\",\"Items\":[{\"Amount\":\"200\",\"DepartmentId\":\"12\",\"DepartmentName\":\"WHISKEY\",\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"103\",\"MerchantFee\":\"0\",\"Name\":\"Chivas 12\",\"PriceLevel\":\"1\",\"Qty\":\"1\",\"SKU\":\"\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Product\"},{\"Amount\":\"90\",\"DepartmentId\":\"7\",\"DepartmentName\":\"CERVEZAS\",\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"68\",\"MerchantFee\":\"0\",\"Name\":\"Bohemia Clara\",\"PriceLevel\":\"1\",\"Qty\":\"1\",\"SKU\":\"\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Product\"},{\"Amount\":\"290\",\"DepartmentId\":\"0\",\"DepartmentName\":\"\",\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"2\",\"MerchantFee\":\"0\",\"Name\":\"MXN\",\"PriceLevel\":null,\"Qty\":\"1\",\"SKU\":\"\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Tender\"}],\"Location\":\"Head office\",\"PosId\":\"2\",\"TransactionEndTime\":\"01\\\/01\\\/0001 00:00:00\",\"TransactionId\":\"0007000010\",\"TransactionStartTime\":\"17\\\/01\\\/2025 05:07:20\"}");
    $json2 = json_decode("{\"ClerkId\":\"1\",\"ClerkName\":\"Daniela \",\"Company\":\"Optima\",\"Items\":[{\"Amount\":\"160\",\"DepartmentId\":\"9\",\"DepartmentName\":\"RON\",\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"90\",\"MerchantFee\":\"0\",\"Name\":\"Habana Club 7 A\",\"PriceLevel\":\"1\",\"Qty\":\"1\",\"SKU\":\"\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Product\"},{\"Amount\":\"0\",\"DepartmentId\":\"9\",\"DepartmentName\":\"RON\",\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"90\",\"MerchantFee\":\"0\",\"Name\":\"Habana Club 7 A\",\"PriceLevel\":\"1\",\"Qty\":\"0\",\"SKU\":\"\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Product\"},{\"Amount\":\"0\",\"DepartmentId\":\"9\",\"DepartmentName\":\"RON\",\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"90\",\"MerchantFee\":\"0\",\"Name\":\"Habana Club 7 A\",\"PriceLevel\":\"1\",\"Qty\":\"0\",\"SKU\":\"\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Product\"},{\"Amount\":\"200\",\"DepartmentId\":\"12\",\"DepartmentName\":\"WHISKEY\",\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"105\",\"MerchantFee\":\"0\",\"Name\":\"Buchanan's 12\",\"PriceLevel\":\"1\",\"Qty\":\"1\",\"SKU\":\"\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Product\"},{\"Amount\":\"495\",\"DepartmentId\":\"3\",\"DepartmentName\":\"FILETES\",\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"18\",\"MerchantFee\":\"0\",\"Name\":\"Filete a la Pimienta\",\"PriceLevel\":\"1\",\"Qty\":\"1\",\"SKU\":\"\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Product\"},{\"Amount\":\"855\",\"DepartmentId\":\"0\",\"DepartmentName\":\"\",\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"2\",\"MerchantFee\":\"0\",\"Name\":\"MXN\",\"PriceLevel\":null,\"Qty\":\"1\",\"SKU\":\"\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Tender\"}],\"Location\":\"Head office\",\"PosId\":\"2\",\"TransactionEndTime\":\"01\\\/01\\\/0001 00:00:00\",\"TransactionId\":\"0007000014\",\"TransactionStartTime\":\"17\\\/01\\\/2025 05:21:02\"}");
    $json3 = json_decode("{\"ClerkId\":\"4\",\"ClerkName\":\"Tecnico \",\"Company\":\"Optima\",\"Items\":[{\"Amount\":\"240\",\"DepartmentId\":\"13\",\"DepartmentName\":\"COGNAC\",\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"107\",\"MerchantFee\":\"0\",\"Name\":\"Martel V.S.O.P\",\"PriceLevel\":\"1\",\"Qty\":\"1\",\"SKU\":\"\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Product\"},{\"Amount\":\"240\",\"DepartmentId\":\"8\",\"DepartmentName\":\"COCTELES\",\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"75\",\"MerchantFee\":\"0\",\"Name\":\"Apperol Schpritz\",\"PriceLevel\":\"1\",\"Qty\":\"1\",\"SKU\":\"\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Product\"},{\"Amount\":\"140\",\"DepartmentId\":\"9\",\"DepartmentName\":\"RON\",\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"84\",\"MerchantFee\":\"0\",\"Name\":\"Bacardi Anejo\",\"PriceLevel\":\"1\",\"Qty\":\"1\",\"SKU\":\"\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Product\"},{\"Amount\":\"4000\",\"DepartmentId\":\"0\",\"DepartmentName\":\"\",\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"3\",\"MerchantFee\":\"0\",\"Name\":\"USD\",\"PriceLevel\":null,\"Qty\":\"1\",\"SKU\":\"\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Tender\"},{\"Amount\":\"-66580.0\",\"DepartmentId\":\"0\",\"DepartmentName\":\"\",\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"2\",\"MerchantFee\":\"0\",\"Name\":\"MXN\",\"PriceLevel\":null,\"Qty\":\"1\",\"SKU\":\"\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Tender\"}],\"Location\":\"Head office\",\"PosId\":\"2\",\"TransactionEndTime\":\"01\\\/01\\\/0001 00:00:00\",\"TransactionId\":\"0007000012\",\"TransactionStartTime\":\"17\\\/01\\\/2025 05:15:47\"}");
    $json4 = json_decode("{\"ClerkId\":\"1\",\"ClerkName\":\"Mesero \",\"Company\":\"Optima\",\"Items\":[{\"Amount\":\"225\",\"DepartmentId\":\"2\",\"DepartmentName\":null,\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"14\",\"MerchantFee\":\"0\",\"Name\":\"Tartaleta de cebolla\",\"PriceLevel\":\"1\",\"Qty\":\"1\",\"SKU\":\"14\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Product\"},{\"Amount\":\"290\",\"DepartmentId\":\"2\",\"DepartmentName\":null,\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"11\",\"MerchantFee\":\"0\",\"Name\":\"Timbal de verduras\",\"PriceLevel\":\"1\",\"Qty\":\"1\",\"SKU\":\"11\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Product\"},{\"Amount\":\"120\",\"DepartmentId\":\"5\",\"DepartmentName\":null,\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"47\",\"MerchantFee\":\"0\",\"Name\":\"Helado\",\"PriceLevel\":\"1\",\"Qty\":\"1\",\"SKU\":\"47\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Product\"},{\"Amount\":\"345\",\"DepartmentId\":\"2\",\"DepartmentName\":null,\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"5\",\"MerchantFee\":\"0\",\"Name\":\"Plato de quesos\",\"PriceLevel\":\"1\",\"Qty\":\"1\",\"SKU\":\"5\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Product\"},{\"Amount\":\"215\",\"DepartmentId\":\"2\",\"DepartmentName\":null,\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"9\",\"MerchantFee\":\"0\",\"Name\":\"Ensalada del chef\",\"PriceLevel\":\"1\",\"Qty\":\"1\",\"SKU\":\"9\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Product\"},{\"Amount\":\"295\",\"DepartmentId\":\"8\",\"DepartmentName\":null,\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"76\",\"MerchantFee\":\"0\",\"Name\":\"Blanc Cassis\",\"PriceLevel\":\"1\",\"Qty\":\"1\",\"SKU\":\"76\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Product\"},{\"Amount\":\"165\",\"DepartmentId\":\"1\",\"DepartmentName\":\"SOPAS\",\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"1\",\"MerchantFee\":\"0\",\"Name\":\"Sopa de cebolla gratinada\",\"PriceLevel\":\"1\",\"Qty\":\"1\",\"SKU\":\"1\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Product\"}],\"Location\":\"Head office\",\"PosId\":\"1\",\"TransactionEndTime\":\"12\\\/05\\\/2025 12:10:28\",\"TransactionId\":\"0005000026\",\"TransactionStartTime\":\"12\\\/05\\\/2025 12:10:22\"}");
    $json5 = json_decode("{\"ClerkId\":\"1\",\"ClerkName\":\"Mesero \",\"Company\":\"Optima\",\"Items\":[{\"Amount\":\"275\",\"DepartmentId\":\"2\",\"DepartmentName\":\"ENTRADAS\",\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"4\",\"MerchantFee\":\"0\",\"Name\":\"Caracoles de borgona\",\"PriceLevel\":\"1\",\"Qty\":\"1\",\"SKU\":\"\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Product\"},{\"Amount\":\"215\",\"DepartmentId\":\"2\",\"DepartmentName\":\"ENTRADAS\",\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"10\",\"MerchantFee\":\"0\",\"Name\":\"Ensalada de espinacas\",\"PriceLevel\":\"1\",\"Qty\":\"1\",\"SKU\":\"\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Product\"},{\"Amount\":\"5000\",\"DepartmentId\":\"0\",\"DepartmentName\":\"\",\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"3\",\"MerchantFee\":\"0\",\"Name\":\"USD\",\"PriceLevel\":null,\"Qty\":\"1\",\"SKU\":\"\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Tender\"},{\"Amount\":\"-83510.0\",\"DepartmentId\":\"0\",\"DepartmentName\":\"\",\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"2\",\"MerchantFee\":\"0\",\"Name\":\"MXN\",\"PriceLevel\":null,\"Qty\":\"1\",\"SKU\":\"\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Tender\"}],\"Location\":\"Head office\",\"PosId\":\"1\",\"TransactionEndTime\":\"12\\\/05\\\/2025 12:10:28\",\"TransactionId\":\"0005000027\",\"TransactionStartTime\":\"12\\\/05\\\/2025 12:44:59\"}");
    $json6 = json_decode("{\"ClerkId\":\"1\",\"ClerkName\":\"Daniela \",\"Company\":\"Optima\",\"Items\":[{\"Amount\":\"480\",\"DepartmentId\":\"3\",\"DepartmentName\":\"FILETES\",\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"16\",\"MerchantFee\":\"0\",\"Name\":\"Filete Natural\",\"PriceLevel\":\"1\",\"Qty\":\"1\",\"SKU\":\"\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Product\"},{\"Amount\":\"495\",\"DepartmentId\":\"3\",\"DepartmentName\":\"FILETES\",\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"22\",\"MerchantFee\":\"0\",\"Name\":\"Filete a la P. Verde\",\"PriceLevel\":\"1\",\"Qty\":\"1\",\"SKU\":\"\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"0\",\"Type\":\"Product\"},{\"Amount\":\"975\",\"DepartmentId\":\"0\",\"DepartmentName\":\"\",\"Discount\":\"0\",\"ExemptedTaxAmount\":\"0\",\"Id\":\"2\",\"MerchantFee\":\"0\",\"Name\":\"MXN\",\"PriceLevel\":null,\"Qty\":\"1\",\"SKU\":\"\",\"Surcharge\":\"0\",\"Taxable\":\"0\",\"Tip\":\"1000\",\"Type\":\"Tender\"}],\"Location\":\"Head office\",\"PosId\":\"1\",\"TransactionEndTime\":\"30\\\/01\\\/2025 15:09:57\",\"TransactionId\":\"0001000038\",\"TransactionStartTime\":\"30\\\/01\\\/2025 17:35:00\"}");
    dd($json1, $json2, $json3, $json4, $json5, $json6);
});

Route::get('/insert_empleado', function(){
    DB::table('tb_empleados')
    ->insert([
        'id_empleado' => 1,
        'nombre' => 'Frank',
        'sucursal_id' => 1
    ]);

    echo "ECHO!!!";
});
