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
use App\Rules\RfcYRegimenCoherentesRule;
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
    public $rfc;
    public $nombre_comercial;
    public $razon_social;
    public $lugar_expedicion;
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
    protected $listeners = ['$refresh', 'cambioIncluirPropina'];

    public function mount($id)
    {
        $this->factura = Factura::find(Crypt::decrypt($id));

        $this->factura_timbrada = $this->factura->estado == 'TIMBRADA';

        $this->rfc = $this->factura->cliente->rfc;
        $this->nombre_comercial = $this->factura->cliente->nombre_comercial ? Crypt::decrypt($this->factura->cliente->nombre_comercial) : '';
        $this->razon_social = $this->factura->cliente->razon_social ? Crypt::decrypt($this->factura->cliente->razon_social) : '';
        $this->lugar_expedicion = $this->factura->cliente->direccion_fiscal->codigo_postal;
        $this->regimen_fiscal_id = $this->factura->cliente->regimen_fiscal_id;
        $this->cfdi_id = $this->factura->cfdi_id;

        $this->regimenesFiscales = RegimenFiscal::orderBy('codigo')->get()->map->only(['label', 'value']);
        $this->cfdis = Cfdi::orderBy('codigo')->get()->map->only(['label', 'value']);
        $this->tiposRelacionFactura = TipoRelacionFactura::orderBy('codigo')->get()->map(function ($element) {
            return [
                'value' => $element->value,
                'label' => $element->label,
            ];
        })->toArray();
        $this->tiposRelacionFactura = array_merge([['value' => '', 'label' => '']], $this->tiposRelacionFactura);
    }

    public function hydrate()
    {
        $this->dispatchBrowserEvent('reApplySelect2');
    }

    public function updated()
    {
        $this->dispatchBrowserEvent('reApplySelect2');
    }
    public function updatedRfc($value)
    {
        $this->nombre_comercial = '';
        $this->razon_social = '';
        $this->lugar_expedicion = '';
        $this->regimen_fiscal_id = null;
        if ($value) {
            $receptor = DB::table('tb_clientes as cliente')
                ->select('cliente.id', 'cliente.nombre_comercial', 'cliente.razon_social', 'cliente.regimen_fiscal_id', 'direccion.codigo_postal')
                ->leftJoin('tb_direcciones as direccion', 'direccion.id', '=', 'cliente.direccion_fiscal_id')
                ->where('cliente.rfc', $value)
                ->get()->first();
            if ($receptor) {
                $this->nombre_comercial = $receptor->nombre_comercial ? Crypt::decrypt($receptor->nombre_comercial) : '';
                $this->razon_social = $receptor->razon_social ? Crypt::decrypt($receptor->razon_social) : '';
                $this->lugar_expedicion = $receptor->codigo_postal;
                $this->regimen_fiscal_id = $receptor->regimen_fiscal_id;
            }
        }
    }

    public function getPropietarioRfcProperty()
    {
        return $this->factura->propietario->rfc;
    }

    public function getPropietarioRazonSocialProperty()
    {
        return Crypt::decrypt($this->factura->propietario->razon_social);
    }

    public function render()
    {
        return view('livewire.timbrar-auto-factura');
    }

    public function totalFacturar()
    {
        $monto = $this->factura->total;
        if ($this->incluir_propina)
            foreach ($this->factura->tickets()->first()->operaciones as $operacion) {
                $tasa_cambio = 1;
                if ($operacion->forma_pago->moneda_id != $operacion->ticket->sucursal->moneda_facturacion_id) {
                    $tipo_cambio = get_tipo_cambio($operacion->forma_pago->moneda_id, $operacion->ticket->sucursal->moneda_facturacion_id, $operacion->ticket->sucursal->id);
                    if ($tipo_cambio->id) {
                        $tasa_cambio = $tipo_cambio->tasa;
                    }
                }
                $monto += round($operacion->propina * $tasa_cambio, 2);
            }

        return number_format($monto, 2);
    }

    public function showModalCfdisRelacionados()
    {
        $this->cfdisModalClass = 'show';
    }

    public function addCfdiRelacionado()
    {
        $this->validate([
            "tipo_relacion_factura_id" => ['required', 'exists:tb_tipo_relacion_facturas,id']
        ], [
            "tipo_relacion_factura_id.required" => 'Campo requerido!',
            "tipo_relacion_factura_id.exists" => 'Tipo de relación no reconocida!',
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
        $data = $this->validate([
            'rfc' => ['required', new RfcRule('ambas')],
            'nombre_comercial' => ['required'],
            'razon_social' => ['required'],
            'lugar_expedicion' => ['required'],
            'regimen_fiscal_id' => ['required', 'exists:tb_regimen_fiscales,id', new RfcYRegimenCoherentesRule($this->rfc)],
            'cfdi_id' => ['required', 'exists:tb_cfdis,id']
        ], [
            'nombre_comercial.required' => 'Campo requerido',
            'razon_social.required' => 'Campo requerido',
            'lugar_expedicion.required' => 'Campo requerido',
            'regimen_fiscal_id.required' => 'Campo requerido',
            'regimen_fiscal_id.exists' => 'Régimen Fiscal no encontrado',
            'cfdi_id.required' => 'Campo requerido.',
            'cfdi_id.exists' => 'Cfdi no encontrado.'
        ]);
        try {
            DB::beginTransaction();

            $receptor = Cliente::withTrashed()->firstOrCreate([
                'rfc' => $data['rfc']
            ]);
            if ($receptor->trashed())
                $receptor->restore();
            $receptor->fill([
                'nombre_comercial' => Crypt::encrypt($data['nombre_comercial']),
                'razon_social' => Crypt::encrypt($data['razon_social']),
                'regimen_fiscal_id' => $data['regimen_fiscal_id'],
                'es_comensal' => 1
            ])->save();
            if ($receptor->direccion_fiscal()->exists()) {
                $receptor->direccion_fiscal->codigo_postal = $data['lugar_expedicion'];
                $receptor->direccion_fiscal->save();
            } else {
                $direccion = Direccion::create([
                    'codigo_postal' => $data['lugar_expedicion']
                ]);
                $receptor->direccion_fiscal_id = $direccion->id;
                $receptor->save();
            }

            $cfdis_relacionados = array_filter($this->cfdis_relacionados, function ($cfdi) {
                return $cfdi != '';
            });

            $ticket = $this->factura->tickets()->first();

            if ($receptor->id != $this->factura->cliente_id) {
                $this->factura->cliente_id = $receptor->id;
                $ticket->comensal_id = $receptor->id;
                $ticket->saveQuietly();
            }
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
                        $descripcion .=  "{$producto->producto->nombre} ($cantidad)" . " | ";
                    }
                }

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
                $iva = 0;
                foreach ($ticket->operaciones as $operacion) {
                    $tasa_cambio = 1;
                    if ($operacion->forma_pago->moneda_id != $operacion->ticket->sucursal->moneda_facturacion_id) {
                        $tipo_cambio = get_tipo_cambio($operacion->forma_pago->moneda_id, $operacion->ticket->sucursal->moneda_facturacion_id, $operacion->ticket->sucursal->id);
                        if ($tipo_cambio->id) {
                            $tasa_cambio = $tipo_cambio->tasa;
                        }
                    }
                    $propina = $operacion->forma_pago->moneda == 'MXN' ? $operacion->propina : (round($operacion->propina * $tasa_cambio, 2));
                    $precio_unitario = $propina / (1 + system_iva() / 100);
                    $subtotal += $precio_unitario;
                    $total += $propina;
                    $iva += ($propina - $precio_unitario);
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
                $this->factura->iva += $iva;
                $this->factura->total += $total;
                $this->factura->saveQuietly();
            }

            $facturador = new Facturador($this->factura->propietario);
            $folio_interno = $this->factura->serie->descripcion . '-' . Factura::internalSheetGenerator($this->factura->serie_id, modo_facturacion() == 1);
            $res = $facturador->timbrarFactura($this->factura->id, $folio_interno);
            if ($res['success']) {
                $this->factura->propietario->comensales()->syncWithoutDetaching($this->factura->cliente_id);
                $this->factura_timbrada = true;
                $this->emit('show-toast', "Factura timbrada satisfactoriamente.");
            } else {
                $this->emit('show-toast', pretty_message($res['message'], 'danger'), 'danger');
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
