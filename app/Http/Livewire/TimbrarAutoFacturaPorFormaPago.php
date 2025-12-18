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

class TimbrarAutoFacturaPorFormaPago extends Component
{
    public Factura $factura;
    public $facturas = [];
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

    public $index_factura_active = 0;
    protected $listeners = ['$refresh', 'cambioIncluirPropina'];

    public function mount($id)
    {
        $this->facturas = DB::table('tb_facturas as factura')
            ->select(
                'factura.id',
                'factura.total',
                'factura.subtotal',
                'factura.iva',
                'factura.moneda',
                'factura.cantidad_letras',
                'factura.direccion_xml',
                'factura.cliente_id',
                'factura.forma_pago_id',
                'factura.tipo_relacion_factura_id',
                'factura.propietario_id',
                'factura.serie_id',
                DB::raw("serie.descripcion as serie"),
                DB::raw("CONCAT_WS(' | ', fp.codigo, fp.descripcion) as forma_pago"),
                DB::raw('0 as incluir_propina'),
                DB::raw('0 as agrupar_conceptos'),
                DB::raw("'' as concepto_agrupado"),
                DB::raw("IF(factura.estado = 'TIMBRADA' || factura.estado = 'COBRADA', 1, 0) as factura_timbrada"),
                DB::raw("'' as tipo_relacion_factura_id"),
                DB::raw("'' as cfdis_relacionados"),
                DB::raw("GROUP_CONCAT(operacion.id) as operaciones"),
                DB::raw("SUM(operacion.propina) as propina")
            )
            ->leftJoin('tb_forma_pagos as fp', 'fp.id', '=', 'factura.forma_pago_id')
            ->leftJoin('tb_series as serie', 'serie.id', '=', 'factura.serie_id')
            ->leftJoin('tb_ticket_operaciones as operacion', 'operacion.factura_id', '=', 'factura.id')
            ->groupBy('factura.id')
            ->whereIn('factura.id', explode(',', Crypt::decrypt($id)))
            ->get()->map(function ($element) {
                $element->cfdis_relacionados = [];
                return (array)$element;
            })->toArray();

        $factura = $this->facturas[0];
        $this->rfc = $factura->cliente->rfc;
        $this->nombre_comercial = $factura->cliente->nombre_comercial ? Crypt::decrypt($factura->cliente->nombre_comercial) : '';
        $this->razon_social = $factura->cliente->razon_social ? Crypt::decrypt($factura->cliente->razon_social) : '';
        $this->lugar_expedicion = $factura->cliente->direccion_fiscal->codigo_postal;
        $this->regimen_fiscal_id = $factura->cliente->regimen_fiscal_id;
        $this->cfdi_id = $factura->cfdi_id;

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
        return $this->facturas[0]->propietario->rfc;
    }

    public function getPropietarioRazonSocialProperty()
    {
        return Crypt::decrypt($this->facturas[0]->propietario->razon_social);
    }

    public function render()
    {
        return view('livewire.timbrar-auto-factura-por-forma-pago');
    }

    public function cambioIncluirPropina($index)
    {
        // $factura = $this->facturas[$index];
        // if ($factura['incluir_propina']) {
        //     $this->facturas[$index]['total'] += $factura['propina'];
        // } else {
        //     $this->facturas[$index]['total'] -= $factura['propina'];
        // }
        // $this->facturas[$index]['subtotal'] = round($this->facturas[$index]['total'] / (1 + system_iva() / 100), 2);
        // $this->facturas[$index]['iva'] = $this->facturas[$index]['total'] - $this->facturas[$index]['subtotal'];
        // $this->facturas[$index]['cantidad_letras'] = convertir_numero_a_letras($this->facturas[$index]['total'], $this->facturas[$index]['moneda']);
    }

    public function montoFactura($index)
    {
        $monto = $this->facturas[$index]['total'];
        if ($this->facturas[$index]['incluir_propina'])
            $monto += $this->factura[$index]['propina'] ? $this->factura[$index]['propina'] : 0;
        return $monto;
    }

    public function showModalCfdisRelacionados($index)
    {
        $this->index_factura_active = $index;
        $this->cfdisModalClass = 'show';
    }

    public function addCfdiRelacionado()
    {
        $this->validate([
            "facturas.$this->index_factura_active.tipo_relacion_factura_id" => ['required', 'exists:tb_tipo_relacion_facturas,id']
        ], [
            "facturas.$this->index_factura_active.tipo_relacion_factura_id.required" => 'Campo requerido!',
            "facturas.$this->index_factura_active.tipo_relacion_factura_id.exists" => 'Tipo de relación no reconocida!',
        ]);
        $this->facturas[$this->index_factura_active]['cfdis_relacionados'][] = '';
    }

    public function removeCfdiRelacionado($index)
    {
        array_splice($this->cfdis_relacionados, $index, 1);
        $this->cfdis_relacionados = array_values($this->cfdis_relacionados);
    }

    public function timbrar($index)
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

            $factura = $this->facturas[$index];

            $cfdis_relacionados = array_filter($factura['cfdis_relacionados'], function ($cfdi) {
                return $cfdi != '';
            });

            $ticket = $factura->ticket_operaciones()->first()->ticket;

            if ($receptor->id != $factura['cliente_id']) {
                $factura['cliente_id'] = $receptor->id;
                $ticket->comensal_id = $receptor->id;
                $ticket->saveQuietly();
            }
            $factura['cfdi_id'] = $this->cfdi_id;
            if (!$factura['tipo_relacion_factura_id'] || count($cfdis_relacionados) == 0) {
                $factura['tipo_relacion_factura_id'] = null;
                $factura['cfdis_relacionados'] = '';
            } else {
                $factura['cfdis_relacionados'] = implode(',', $cfdis_relacionados);
            }

            DB::table('tb_facturas')
                ->where('id', $factura['id'])
                ->update(array_merge(
                    Arr::except($factura, [
                        'serie',
                        'serie_id',
                        'direccion_xml',
                        'propietario_id',
                        'forma_pago',
                        'incluir_propina',
                        'agrupar_conceptos',
                        'concepto_agrupado',
                        'factura_timbrada',
                        'operaciones',
                        'propina'
                    ]),
                    Arr::only($data, ['lugar_expedicion', 'cfdi_id'])
                ));

            if ($factura['agrupar_conceptos']) {
                DB::table('tb_factura_conceptos')->insert([
                    'cantidad' => 1,
                    'precio_unitario' => $factura['subtotal'],
                    'descripcion' => $factura['concepto_agrupado'],
                    'clave_prod_serv_id' => 191,
                    'clave_unidad_id' => 2,
                    'objeto_impuesto_id' => 2,
                    'factura_id' => $factura['id']
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

                DB::table('tb_factura_conceptos')->insert([
                    'cantidad' => 1,
                    'precio_unitario' => $factura['subtotal'],
                    'descripcion' => utf8_decode(Str::replaceLast(" | ", '', $descripcion)),
                    'clave_prod_serv_id' => 191,
                    'clave_unidad_id' => 2,
                    'objeto_impuesto_id' => 2,
                    'factura_id' => $factura['id']
                ]);
            }

            if ($factura['incluir_propina'] && $factura['propina'] > 0) {
                $precio_unitario = round($factura['propina'] / (1 + system_iva() / 100), 2);
                DB::table('tb_factura_conceptos')->insert([
                    'cantidad' => 1,
                    'precio_unitario' => $precio_unitario,
                    'descripcion' => "Propina",
                    'clave_prod_serv_id' => 191,
                    'clave_unidad_id' => 2,
                    'objeto_impuesto_id' => 2,
                    'factura_id' => $factura['id']
                ]);

                DB::table('tb_facturas')
                    ->where('id', $factura['id'])
                    ->update([
                        'subtotal' => $factura['subtotal'] + $precio_unitario,
                        'iva' => $factura['iva'] + ($factura['propina'] - $precio_unitario),
                        'total' => $factura['total'] + $factura['propina'],
                        'cantidad_letras' => convertir_numero_a_letras($factura['total'] + $factura['propina'], $factura['moneda'])
                    ]);
            }

            $facturador = new Facturador(Sucursal::find($factura['propietario_id']));
            $folio_interno = $factura['serie'] . '-' . Factura::internalSheetGenerator($factura['serie_id'], modo_facturacion() == 1);
            $res = $facturador->timbrarFactura($factura['id'], $folio_interno);
            if ($res['success']) {
                $this->facturas[$index]['factura_timbrada'] = true;
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

    public function descargarPDF($index)
    {
        return response()->download(public_path("/" . Factura::generateFacturaPdf($this->facturas[$index]['id'])));
    }
    public function descargarXML($index)
    {
        return response()->download(Storage::disk('public')->path($this->facturas[$index]['direccion_xml']));
    }
}
