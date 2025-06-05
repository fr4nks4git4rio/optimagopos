<?php

namespace App\Http\Livewire;

use App\Models\Cfdi;
use App\Models\Cliente;
use App\Models\Direccion;
use App\Models\Estado;
use App\Models\Factura;
use App\Models\Localidad;
use App\Models\Municipio;
use App\Models\Producto;
use App\Models\RegimenFiscal;
use App\Models\Sucursal;
use App\Models\Ticket;
use App\Models\TicketProducto;
use App\Models\TicketProductoCorreccion;
use App\Models\TipoRelacionFactura;
use App\Rules\RfcRule;
use App\Rules\RuleUnique;
use App\Services\Timbrado\Facturador;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class TimbrarAutoFactura extends Component
{
    public Factura $factura;
    public $regimen_fiscal_id;
    public $cfdi_id;
    public $tipo_relacion_factura_id;
    public $regimenesFiscales = [];
    public $cfdis = [];
    public $tiposRelacionFactura = [];

    public $posiblesConceptos = ['CONSUMO DE ALIMENTO Y BEBIDAS', 'CONSUMO DE BEBIDAS', 'CONSUMO DE ALIMENTOS', 'DIVERSOS'];

    public $cfdis_relacionados = [];
    public $incluir_propina = false;
    public $agrupar_conceptos = true;
    public $concepto_agrupado = 'CONSUMO DE ALIMENTO Y BEBIDAS';
    public $cfdisModalClass = '';
    public $factura_timbrada = false;
    protected $listeners = ['$refresh'];

    public function mount($id)
    {
        $this->factura = Factura::find($id);
        $this->factura_timbrada = $this->factura->estado == 'TIMBRADA';

        $this->regimen_fiscal_id = $this->factura->cliente->regimen_fiscal_id;
        $this->cfdi_id = $this->factura->cfdi_id;

        $this->regimenesFiscales = RegimenFiscal::orderBy('codigo')->get()->map->only(['label', 'value']);
        $this->cfdis = Cfdi::orderBy('codigo')->get()->map->only(['label', 'value']);
        $this->tiposRelacionFactura = TipoRelacionFactura::orderBy('codigo')->get()->map->only(['label', 'value']);
    }

    public function hydrate()
    {
        $this->dispatchBrowserEvent('reApplySelect2');
    }

    public function updated()
    {
        $this->dispatchBrowserEvent('reApplySelect2');
    }

    public function getPropietarioRfcProperty()
    {
        return $this->factura->propietario->rfc;
    }

    public function getPropietarioRazonSocialProperty()
    {
        return Crypt::decrypt($this->factura->propietario->razon_social);
    }

    public function getClienteRfcProperty()
    {
        return $this->factura->cliente()->first()->rfc;
    }

    public function getClienteRazonSocialProperty()
    {
        return Crypt::decrypt($this->factura->cliente()->first()->razon_social);
    }

    public function getClienteCodigoPostalProperty()
    {
        return $this->factura->cliente()->first()->codigo_postal;
    }

    public function render()
    {
        return view('livewire.timbrar-auto-factura');
    }

    public function addCfdiRelacionado()
    {
        $this->validate([
            'tipo_relacion_factura_id' => ['required', 'exists:tb_tipo_relacion_facturas,id']
        ], [
            'tipo_relacion_factura_id.required' => 'Campo requerido!',
            'tipo_relacion_factura_id.exists' => 'Tipo de relación no reconocida!',
        ]);
        $this->cfdis_relacionados[] = '';
    }

    public function removeCfdiRelacionado($index)
    {
        array_splice($this->cfdis_relacionados, $index, 1);
        $this->cfdis_relacionados = array_values($this->cfdis_relacionados);
    }

    public function timbrar()
    {

        $this->validate([
            'cfdi_id' => ['required', 'exists:tb_cfdis,id']
        ], [
            'cfdi_id.required' => 'Campo requerido.',
            'cfdi_id.exists' => 'Cfdi no encontrado.'
        ]);
        try {
            DB::beginTransaction();

            $cfdis_relacionados = array_filter($this->cfdis_relacionados, function ($cfdi) {
                return $cfdi != '';
            });

            $ticket = $this->factura->tickets()->first();

            $this->factura->folio_interno = $this->factura->serie->descripcion . '-' . Factura::internalSheetGenerator($this->factura->serie_id, modo_facturacion() == 1);
            $this->factura->cfdi_id = $this->cfdi_id;
            if ($this->tipo_relacion_factura_id && count($cfdis_relacionados) > 0) {
                $this->factura->tipo_relacion_factura_id = $this->tipo_relacion_factura_id;
                $this->factura->cfdis_relacionados = implode(",", $cfdis_relacionados);
            } else {
                $this->tipo_relacion_factura_id = null;
                $this->factura->cfdis_relacionados = '';
            }
            $this->factura->saveQuietly();

            $this->factura->factura_conceptos()->delete();
            if ($this->agrupar_conceptos) {
                $this->factura->factura_conceptos()->create([
                    'cantidad' => 1,
                    'precio_unitario' => $this->factura->subtotal,
                    'descripcion' => $this->concepto_agrupado,
                    'clave_prod_serv_id' => 191,
                    'clave_unidad_id' => 2,
                    'objeto_impuesto_id' => 2
                ]);
            } else {
                $descripcion = '';
                // $subtotal = 0;
                foreach ($ticket->productos as $producto) {
                    $cantidad = $producto->cantidad;
                    $precio = $producto->precio;
                    if ($producto->correcciones()->count() > 0) {
                        $producto->correcciones->map(function (TicketProductoCorreccion $correccion) use (&$cantidad, &$precio) {
                            $cantidad -= $correccion->cantidad;
                            $precio -= $correccion->precio;
                        });
                    }
                    if ($cantidad > 0 && $precio > 0) {
                        // $precio_unitario = $precio / $cantidad;
                        // $precio_unitario = round($precio_unitario / (1 + $this->factura->porciento_iva / 100), 2);
                        // $subtotal += $precio_unitario * $cantidad;
                        // while ($cantidad > 0) {
                        //     $this->factura->factura_conceptos()->create([
                        //         'cantidad' => 1,
                        //         'precio_unitario' => $precio_unitario,
                        //         'descripcion' => utf8_decode($producto->producto->nombre),
                        //         'clave_prod_serv_id' => 191,
                        //         'clave_unidad_id' => 2,
                        //         'objeto_impuesto_id' => 2
                        //     ]);
                        //     $cantidad--;
                        // }
                        $descripcion .=  "{$producto->producto->nombre} ($cantidad)" . " | ";
                    }
                }

                // if ($this->factura->subtotal != $subtotal) {
                //     $this->factura->factura_conceptos()->orderByDesc('created_at')->first()->fill([
                //         'precio_unitario' => $this->factura->factura_conceptos()->latest()->first()->precio_unitario + ($this->factura->subtotal - $subtotal)
                //     ])->save();
                // }

                $this->factura->factura_conceptos()->create([
                    'cantidad' => 1,
                    'precio_unitario' => $this->factura->subtotal,
                    'descripcion' => utf8_decode(Str::replaceLast(" | ", '', $descripcion)),
                    'clave_prod_serv_id' => 191,
                    'clave_unidad_id' => 2,
                    'objeto_impuesto_id' => 2
                ]);
            }

            if ($this->incluir_propina) {
                $subtotal = 0;
                $total = 0;
                foreach ($ticket->propinas as $propina) {
                    $precio_unitario = $propina->monto / (1 + system_iva() / 100);
                    $subtotal += $precio_unitario;
                    $total += $propina->monto;
                    $this->factura->factura_conceptos()->create([
                        'cantidad' => 1,
                        'precio_unitario' => $precio_unitario,
                        'descripcion' => "Propina",
                        'clave_prod_serv_id' => 191,
                        'clave_unidad_id' => 2,
                        'objeto_impuesto_id' => 2
                    ]);
                }
                $this->factura->subtotal += $subtotal;
                $this->factura->total += $total;
                $this->factura->saveQuietly();
            }

            $facturador = new Facturador($this->factura->propietario);
            $res = $facturador->timbrarFactura($this->factura->id, $this->factura->folio_interno);
            if ($res['success']) {
                $this->factura_timbrada = true;
                $this->emit('show-toast', "Factura timbrada satisfactoriamente.");
            } else {
                $this->emit('show-toast', $res['message'], 'danger');
            }
            DB::commit();
        } catch (Exception $e) {
            $this->emit('show-toast', 'Ha ocurrido un error intentando timbrar la Factura.', 'danger');
            Log::error("Ha ocurrido un error intentando timbrar una Factura. Error: {$e->getMessage()}");
            DB::rollBack();
        }
    }

    public function descargarPDF()
    {
        return response()->download(public_path("/" . Factura::generateFacturaPdf($this->factura->id)));
    }
    public function descargarXML()
    {
        return response()->download(Storage::disk('public')->path($this->factura->direccion_xml));
    }
}
