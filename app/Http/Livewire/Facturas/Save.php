<?php

namespace App\Http\Livewire\Facturas;

use App\Exports\FacturaEmitidaExport;
use App\Http\Libraries\Pdf;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Sucursal;
use App\Models\SucursalFormaPago;
use App\Models\TicketOperacion;
use App\Services\Timbrado\Facturador;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Save extends Component
{
    public Factura $factura;
    public $estado;
    public $fecha_emision;
    public $lugar_expedicion;
    public $moneda;
    public $anio;
    public $porciento_iva;
    public $tipo_cambio;
    public $subtotal;
    public $iva;
    public $total;
    public $cantidad_letras;
    public $comentarios;
    public $propietario_id;
    public $cliente_id;
    public $serie_id;
    public $cfdi_id;
    public $metodo_pago_id;
    public $forma_pago_id;
    public $tipo_comprobante_id = 1;
    public $periodicidad_id;
    public $mes_id;
    public $user_id;
    public $fecha_inicio;
    public $fecha_fin;
    public $forma_pago;

    public $concepto = [
        'id' => null,
        'cantidad' => 1,
        'precio_unitario' => '',
        'descripcion' => '',
        'clave_prod_serv_id' => null,
        'clave_unidad_id' => null,
        'objeto_impuesto_id' => null
    ];
    public $formasPagoSucursal = [];
    public $metodosPago = [];
    public $formasPago = [];
    public $sucursales = [];
    public $series = [];
    public $usosCfdi = [];
    public $periodicidades = [];
    public $meses = [];
    public $anios = [];
    public $clavesProdServ = [];
    public $clavesUnidad = [];
    public $objetosImpuesto = [];
    public $tickets = [];
    public $factura_conceptos = [];
    public $validacion_operaciones_pendientes = '';
    public $modalDeleteConceptoClass = '';
    public $regimen_receptor = '';
    public $total_resumen_tickets = '';
    public $index_concepto_activo = null;
    protected $listeners = ['$refresh'];

    public function mount($id = null)
    {
        if ($id) {
            $this->factura = Factura::find($id);
            $this->estado = $this->factura->estado == 'PRECAPTURADA' ? 'CAPTURADA' : $this->factura->estado;
            $this->fecha_emision = $this->factura->fecha_emision;
            $this->lugar_expedicion = $this->factura->lugar_expedicion;
            $this->moneda = $this->factura->moneda;
            $this->tipo_cambio = $this->factura->tipo_cambio;
            $this->porciento_iva = $this->factura->porciento_iva;
            $this->cantidad_letras = $this->factura->cantidad_letras;
            $this->comentarios = $this->factura->comentarios;
            $this->anio = $this->factura->anio;
            $this->propietario_id = $this->factura->propietario_id;
            $this->cliente_id = $this->factura->cliente_id;
            $this->serie_id = $this->factura->serie_id;
            $this->cfdi_id = $this->factura->cfdi_id;
            $this->metodo_pago_id = $this->factura->metodo_pago_id;
            $this->forma_pago_id = $this->factura->forma_pago_id;
            $this->periodicidad_id = $this->factura->periodicidad_id;
            $this->mes_id = $this->factura->mes_id;
            $this->user_id = $this->factura->user_id;
            $this->factura_conceptos = $this->factura->factura_conceptos()->get()->map(function ($element) {
                $element->clave_prod_serv = DB::table('tb_clave_prod_servs')->selectRaw("CONCAT_WS(' | ', codigo, nombre) as nombre")->where('id', $element->clave_prod_serv_id)->get()->first()->nombre;
                $element->clave_unidad = DB::table('tb_clave_unidades')->selectRaw("CONCAT_WS(' | ', codigo, descripcion) as nombre")->where('id', $element->clave_unidad_id)->get()->first()->nombre;
                $element->objeto_impuesto = DB::table('tb_objetos_impuesto')->selectRaw("CONCAT_WS(' | ', clave, descripcion) as nombre")->where('id', $element->objeto_impuesto_id)->get()->first()->nombre;
                return [
                    'id' => $element->id,
                    'cantidad' => $element->cantidad,
                    'precio_unitario' => $element->precio_unitario,
                    'descripcion' => $element->descripcion,
                    'clave_prod_serv_id' => $element->clave_prod_serv_id,
                    'clave_prod_serv' => DB::table('tb_clave_prod_servs')->selectRaw("CONCAT_WS(' | ', codigo, nombre) as nombre")->where('id', $element->clave_prod_serv_id)->get()->first()->nombre,
                    'clave_unidad_id' => $element->clave_unidad_id,
                    'clave_unidad' => DB::table('tb_clave_unidades')->selectRaw("CONCAT_WS(' | ', codigo, descripcion) as nombre")->where('id', $element->clave_unidad_id)->get()->first()->nombre,
                    'objeto_impuesto_id' => $element->objeto_impuesto_id,
                    'objeto_impuesto' => DB::table('tb_objetos_impuesto')->selectRaw("CONCAT_WS(' | ', clave, descripcion) as nombre")->where('id', $element->objeto_impuesto_id)->get()->first()->nombre
                ];
            })->toArray();

            $this->tickets = DB::table('tb_tickets as ticket')
                ->selectRaw('ticket.id,
                DATE_FORMAT(ticket.created_at, "%d/%m/%Y") AS fecha,
                to.sucursal_forma_pago_id,
                sfp.nombre as forma_pago,
                0 AS subtotal,
                0 AS iva,
                SUM(IFNULL(to.monto, 0)) AS total,
                group_concat( distinct to.id ) as operaciones')
                ->join('tb_ticket_operaciones as to', 'ticket.id', '=', 'to.ticket_id')
                ->leftJoin('tb_sucursal_forma_pagos as sfp', 'to.sucursal_forma_pago_id', '=', 'sfp.id')
                ->where('to.factura_id', $id)
                ->groupBy('to.sucursal_forma_pago_id', 'ticket.created_at')
                ->orderBy('ticket.created_at', 'asc')
                ->get()
                ->map(function ($value, $key) {
                    $value->subtotal = round($value->total / (1 + system_iva() / 100), 2);
                    $value->iva = round($value->total - $value->subtotal, 2);
                    return (array)$value;
                })->toArray();
        } else {
            $this->factura = new Factura();
            $this->estado = 'CAPTURADA';
            $this->fecha_emision = now();
            $this->cliente_id = DB::table('tb_clientes')->select('id')->where('rfc', 'XAXX010101000')->get()->first()->id;
            $this->anio = today()->year;
            $this->user_id = user()->id;
            $this->porciento_iva = system_iva();
        }

        $this->sucursales = DB::table('tb_sucursales')
            ->select('id as value', 'nombre_comercial as label')
            ->where('cliente_id', user()->cliente_id)
            ->get()->map(function ($value, $key) {
                $value->label = Crypt::decrypt($value->label);
                return (array)$value;
            })->toArray();
        $this->series = DB::table('tb_series')
            ->select('id as value', 'descripcion as label')
            ->get()->map(function ($value, $key) {
                return (array)$value;
            })->toArray();
        $this->metodosPago = DB::table('tb_metodo_pagos')
            ->select('id as value', DB::raw("CONCAT_WS(' | ', codigo, descripcion) as label"))
            ->get()->map(function ($value, $key) {
                return (array)$value;
            })->toArray();
        $this->formasPago = DB::table('tb_forma_pagos')
            ->select('id as value', DB::raw("CONCAT_WS(' | ', codigo, descripcion) as label"))
            ->get()->map(function ($value, $key) {
                return (array)$value;
            })->toArray();
        $this->usosCfdi = DB::table('tb_cfdis')
            ->select('id as value', DB::raw("CONCAT_WS(' | ', codigo, descripcion) as label"))
            ->get()->map(function ($value, $key) {
                return (array)$value;
            })->toArray();
        $this->periodicidades = DB::table('tb_periodicidades_factura')
            ->select('id as value', DB::raw("CONCAT_WS(' | ', clave, descripcion) as label"))
            ->get()->map(function ($value, $key) {
                return (array)$value;
            })->toArray();
        $this->meses = DB::table('tb_meses')
            ->select('id as value', DB::raw("CONCAT_WS(' | ', clave, descripcion) as label"))
            ->get()->map(function ($value, $key) {
                return (array)$value;
            })->toArray();
        $this->anios = [today()->year, today()->year - 1];
        $this->clavesProdServ = DB::table('tb_clave_prod_servs')
            ->select('id as value', DB::raw("CONCAT_WS(' | ', codigo, nombre) as label"))
            ->get()->map(function ($value, $key) {
                return (array)$value;
            })->toArray();
        $this->clavesUnidad = DB::table('tb_clave_unidades')
            ->select('id as value', DB::raw("CONCAT_WS(' | ', codigo, descripcion) as label"))
            ->get()->map(function ($value, $key) {
                return (array)$value;
            })->toArray();
        $this->objetosImpuesto = DB::table('tb_objetos_impuesto')
            ->select('id as value', DB::raw("CONCAT_WS(' | ', clave, descripcion) as label"))
            ->get()->map(function ($value, $key) {
                return (array)$value;
            })->toArray();
    }

    public function updatedPropietarioId($value)
    {
        $this->lugar_expedicion = '';
        $this->formasPagoSucursal = [];
        if ($value) {
            $this->formasPagoSucursal = DB::table('tb_sucursal_forma_pagos as sfp')
                ->select('sfp.id as value', 'sfp.nombre as label')
                ->where('sfp.sucursal_id', $value)
                ->get()->map(function ($value, $key) {
                    return (array)$value;
                })->toArray();
            $this->lugar_expedicion = DB::table('tb_clientes as cliente')
                ->select('direccion.codigo_postal')
                ->leftJoin('tb_direcciones as direccion', 'direccion.id', '=', 'cliente.direccion_fiscal_id')
                ->where('cliente.id', $value)
                ->get()->first()->codigo_postal;
            $this->dispatchBrowserEvent('reApplySelect2');
        }
    }

    public function updatedFormaPago($value)
    {
        $this->moneda = '';
        $this->forma_pago_id = '';
        $this->tipo_cambio = 1;
        if ($value) {
            $forma_pago = DB::table('tb_sucursal_forma_pagos')
                ->select('id', 'moneda', 'forma_pago_id')
                ->where('id', $value)
                ->get()->first();
            $this->moneda = optional($forma_pago)->moneda;
            $this->forma_pago_id = optional($forma_pago)->forma_pago_id;
            $this->tipo_cambio = get_tipo_cambio()->tasa;
        }
    }

    public function getReceptorProperty()
    {
        $receptor = DB::table('tb_clientes as cliente')
            ->select(
                'cliente.id',
                'cliente.razon_social',
                'cliente.rfc',
                DB::raw("CONCAT_WS(' | ', regimen_fiscal.codigo, regimen_fiscal.descripcion) as regimen_fiscal"),
                DB::raw("CONCAT_WS(', ', direccion.calle, direccion.no_exterior, direccion.no_interior, direccion.colonia, localidad.nombre, municipio.nombre, estado.nombre, CONCAT('CP:',direccion.codigo_postal)) as direccion"),
                DB::raw("CONCAT(
                IF(direccion.calle != '', CONCAT(direccion.calle, ', '), ''),
                IF(direccion.no_exterior != '', CONCAT(direccion.no_exterior, ', '), ''),
                IF(direccion.no_interior != '', CONCAT(direccion.no_interior, ', '), ''),
                IF(direccion.colonia != '', CONCAT(direccion.colonia, ', '), ''),
                IF(localidad.nombre != '', CONCAT(localidad.nombre, ', '), ''),
                IF(municipio.nombre != '', CONCAT(municipio.nombre, ', '), ''),
                IF(estado.nombre != '', CONCAT(estado.nombre, ', '), ''),
                CONCAT('CP: ',direccion.codigo_postal)) as direccion")
            )
            ->leftJoin('tb_regimen_fiscales as regimen_fiscal', 'regimen_fiscal.id', '=', 'cliente.regimen_fiscal_id')
            ->leftJoin('tb_direcciones as direccion', 'direccion.id', '=', 'cliente.direccion_fiscal_id')
            ->leftJoin('tb_localidades as localidad', 'localidad.id', '=', 'direccion.localidad_id')
            ->leftJoin('tb_municipios as municipio', 'municipio.id', '=', 'direccion.municipio_id')
            ->leftJoin('tb_estados as estado', 'estado.id', '=', 'direccion.estado_id')
            ->where('cliente.id', $this->cliente_id)->get()->map(function ($element) {
                $element->razon_social = Crypt::decrypt($element->razon_social);
                return $element;
            })->first();
        return $receptor;
    }

    public function getNombreReceptorProperty()
    {
        return optional($this->getReceptorProperty())->razon_social;
    }
    public function getRfcReceptorProperty()
    {
        return optional($this->getReceptorProperty())->rfc;
    }
    public function getDireccionFiscalReceptorProperty()
    {
        return optional($this->getReceptorProperty())->direccion;
    }
    public function getRegimenFiscalReceptorProperty()
    {
        return optional($this->getReceptorProperty())->regimen_fiscal;
    }

    public function getFechaEmisionStrProperty()
    {
        return $this->fecha_emision->format('d/m/Y H:i');
    }

    public function getSubtotalFacturarProperty()
    {
        return array_reduce($this->tickets, function ($carry, $item) {
            return $carry + $item['subtotal'];
        }, 0);
    }
    public function getIvaFacturarProperty()
    {
        return array_reduce($this->tickets, function ($carry, $item) {
            return $carry + $item['iva'];
        }, 0);
    }
    public function getTotalFacturarProperty()
    {
        return array_reduce($this->tickets, function ($carry, $item) {
            return $carry + $item['total'];
        }, 0);
    }
    public function getSubtotalFacturaProperty()
    {
        return array_reduce($this->factura_conceptos, function ($carry, $item) {
            return $carry + $item['precio_unitario'];
        }, 0);
    }
    public function getIvaFacturaProperty()
    {
        return $this->subtotal_factura * system_iva() / 100;
    }
    public function getImporteLetrasFacturaProperty()
    {
        return convertir_numero_a_letras($this->total_factura, $this->moneda);
    }
    public function getTotalFacturaProperty()
    {
        return round($this->subtotal_factura + $this->iva_factura, 2);
    }

    public function render()
    {
        return view('livewire.facturas.save');
    }

    public function guardar()
    {
        $this->subtotal = $this->subtotal_factura;
        $this->iva = round($this->iva_factura, 2);
        $this->total = $this->total_factura;
        $this->cantidad_letras = $this->importe_letras_factura;
        $this->regimen_receptor = $this->regimen_fiscal_receptor;
        $this->total_resumen_tickets = $this->total_facturar;
        $data = $this->validate([
            'estado' => ['required'],
            'fecha_emision' => ['required'],
            'propietario_id' => ['required', 'exists:tb_sucursales,id'],
            'total_resumen_tickets' => ['required', 'numeric', 'gt:0'],
            'porciento_iva' => ['nullable'],
            'subtotal' => ['required', 'numeric', 'gt:0'],
            'iva' => ['required', 'numeric', 'gt:0'],
            'total' => ['required', 'numeric', 'gt:0', 'same:total_resumen_tickets'],
            'cantidad_letras' => ['required'],
            'lugar_expedicion' => ['required'],
            'regimen_receptor' => ['required'],
            'moneda' => ['required'],
            'tipo_cambio' => ['nullable', 'required_if:moneda,USD'],
            'cfdi_id' => ['required', 'exists:tb_cfdis,id'],
            'metodo_pago_id' => ['required', 'exists:tb_metodo_pagos,id'],
            'forma_pago_id' => ['required', 'exists:tb_forma_pagos,id'],
            'cliente_id' => ['required', 'exists:tb_clientes,id'],
            'tipo_comprobante_id' => ['required'],
            'serie_id' => ['required', 'exists:tb_series,id'],
            'periodicidad_id' => ['required', 'exists:tb_periodicidades_factura,id'],
            'mes_id' => ['required', 'exists:tb_meses,id'],
            'user_id' => ['required', 'exists:tb_usuarios,id'],
            'anio' => ['required'],
            'factura_conceptos' => ['required', 'array', 'min:1'],
            'factura_conceptos.*.id' => ['nullable'],
            'factura_conceptos.*.cantidad' => ['required'],
            'factura_conceptos.*.clave_prod_serv_id' => ['required', 'exists:tb_clave_prod_servs,id'],
            'factura_conceptos.*.clave_unidad_id' => ['required', 'exists:tb_clave_unidades,id'],
            'factura_conceptos.*.objeto_impuesto_id' => ['required', 'exists:tb_objetos_impuesto,id'],
            'factura_conceptos.*.descripcion' => ['nullable'],
            'factura_conceptos.*.precio_unitario' => ['required', 'numeric', 'gt:0'],
            'tickets' => ['required', 'array', 'min:1'],
            'tickets.*.operaciones' => ['required'],
            'comentarios' => 'nullable'
        ], [
            'fecha_emision.required' => 'Campo requerido',
            'propietario_id.required' => 'Campo requerido',
            'propietario_id.exists' => 'Sucursal no encontrada',
            'subtotal.required' => 'Valor requerido',
            'iva.required' => 'Valor requerido',
            'total.required' => 'Valor requerido',
            'subtotal.gt' => 'Valor superior',
            'iva.gt' => 'Valor superior',
            'total.gt' => 'Valor superior',
            'total.same' => 'El total a facturar no se corresponde con el total de Resumen de Tickets',
            'cantidad_letras.required' => 'Campo requerido',
            'lugar_expedicion.required' => 'Campo requerido',
            'regimen_receptor.required' => 'Campo requerido',
            'cliente_id.required' => 'Campo requerido',
            'cliente_id.exists' => 'Cliente no encontrado',
            'serie_id.required' => 'Campo requerido',
            'serie_id.exists' => 'Serie no encontrado',
            'moneda.required' => 'Campo requerido',
            'metodo_pago_id.required' => 'Campo requerido',
            'metodo_pago_id.exists' => 'Método de pago no encontrado',
            'forma_pago_id.required' => 'Campo requerido',
            'forma_pago_id.exists' => 'Forma de pago no encontrado',
            'cfdi_id.required' => 'Campo requerido',
            'cfdi_id.exists' => 'CFDI no encontrado',
            'tipo_cambio.required_if' => 'Campo requerido',
            'periodicidad_id.required' => 'Campo requerido',
            'periodicidad_id.exists' => 'Periodicidad no encontrada',
            'mes_id.required' => 'Campo requerido',
            'mes_id.exists' => 'Mes no encontrado',
            'user_id.required' => 'Usuario no encontrado',
            'user_id.exists' => 'Usuario no encontrado',
            'anio.required' => 'Campo requerido',
            'factura_conceptos.required' => 'Debe adicionar la menos un concepto',
            'factura_conceptos.min' => 'Debe adicionar la menos un concepto',
            'factura_conceptos.*.cantidad.required' => 'Campo requerido',
            'factura_conceptos.*.clave_prod_serv_id.required' => 'Campo requerido',
            'factura_conceptos.*.clave_prod_serv_id.exists' => 'Clave no encontrada',
            'factura_conceptos.*.clave_unidad_id.required' => 'Campo requerido',
            'factura_conceptos.*.clave_unidad_id.exists' => 'Clave no encontrada',
            'factura_conceptos.*.objeto_impuesto_id.required' => 'Campo requerido',
            'factura_conceptos.*.objeto_impuesto_id.exists' => 'Objeto no encontrado',
            'factura_conceptos.*.precio_unitario.required' => 'Campo requerido',
            'factura_conceptos.*.precio_unitario.gt' => 'Entre un valor superior',
            'tickets.required' => 'La búsqueda debe arrojar como resultado al menos un ticket a facturar',
            'tickets.min' => 'La búsqueda debe arrojar como resultado al menos un ticket a facturar',
        ]);

        $this->factura->fill(Arr::except($data, [
            'total_resumen_tickets',
            'regimen_receptor',
            'factura_conceptos',
            'tickets'
        ]))->save();

        $conceptos_ids = [];
        foreach ($data['factura_conceptos'] as $concepto) {
            if ($concepto['id']) {
                DB::table('tb_factura_conceptos')
                    ->where('id', $concepto['id'])
                    ->update(array_merge(Arr::except($concepto, ['id']), ['updated_at' => now()]));
                $id = $concepto['id'];
            } else {
                $id = DB::table('tb_factura_conceptos')
                    ->insertGetId(array_merge(Arr::except($concepto, ['id']), ['factura_id' => $this->factura->id, 'created_at' => now()]));
            }
            $conceptos_ids[] = $id;
        }

        $this->factura->factura_conceptos()
            ->whereNotIn('id', $conceptos_ids)
            ->delete();

        $this->factura->ticket_operaciones->map(function (TicketOperacion $operacion) {
            $operacion->factura_id = null;
            $operacion->save();
        });
        foreach ($data['tickets'] as $ticket) {
            DB::table('tb_ticket_operaciones')
                ->whereIn('id', explode(',', $ticket['operaciones']))
                ->update(['factura_id' => $this->factura->id, 'updated_at' => now()]);
        }

        $this->emit('show-toast', 'Factura guardada');
        $this->goToList();
    }

    public function loadImporteFacturar()
    {
        $this->validate([
            'propietario_id' => ['required', 'exists:tb_sucursales,id'],
            'fecha_inicio' => 'required',
            'fecha_fin' => 'required',
            'forma_pago' => 'required'
        ], [
            'propietario_id.required' => 'Campo requerido',
            'propietario_id.exists' => 'Sucursal no encontrada',
            'fecha_inicio.required' => 'Campo requerido',
            'fecha_fin.required' => 'Campo requerido',
            'forma_pago.required' => 'Campo requerido',
        ]);
        $this->validacion_operaciones_pendientes = "";
        $whereFP = " (to.sucursal_forma_pago_id = " . $this->forma_pago . ") ";

        $records = DB::table('tb_tickets as ticket')
            ->selectRaw('MIN(ticket.created_at) as fecha_min,
                    MAX(ticket.created_at) as fecha_max,
                    group_concat( distinct ticket.created_at ) as fechas')
            ->join('tb_ticket_operaciones as to', 'ticket.id', '=', 'to.ticket_id')
            ->where('to.factura_id', null)
            ->whereRaw($whereFP)
            ->whereDate('ticket.created_at', '<', $this->fecha_inicio)
            ->get();

        if ($records[0]->fecha_min != "") {
            $forma_pago = SucursalFormaPago::find($this->forma_pago);
            $fecha_min = Carbon::createFromFormat('Y-m-d H:i:s', $records[0]->fecha_min)->format('d/m/Y');
            $fecha_max = Carbon::createFromFormat('Y-m-d H:i:s', $records[0]->fecha_max)->format('d/m/Y');
            $this->validacion_operaciones_pendientes = "Existen tickets en $forma_pago->tipo sin facturar correspondientes al período del <strong>" . $fecha_min . '</strong> al <strong>' . $fecha_max . '</strong>';
        }

        $this->tickets = DB::table('tb_tickets as ticket')
            ->selectRaw('ticket.id,
                DATE_FORMAT(ticket.created_at, "%d/%m/%Y") AS fecha,
                to.sucursal_forma_pago_id,
                sfp.nombre as forma_pago,
                0 AS subtotal,
                0 AS iva,
                SUM(IFNULL(to.monto, 0)) AS total,
                group_concat( distinct to.id ) as operaciones')
            ->join('tb_ticket_operaciones as to', 'ticket.id', '=', 'to.ticket_id')
            ->leftJoin('tb_sucursal_forma_pagos as sfp', 'to.sucursal_forma_pago_id', '=', 'sfp.id')
            ->where('to.factura_id', null)
            ->whereRaw($whereFP)
            ->whereBetween('ticket.created_at', [$this->fecha_inicio . ' 00:00:00', $this->fecha_fin . ' 23:59:59'])
            ->groupBy('to.sucursal_forma_pago_id', 'ticket.created_at')
            ->orderBy('ticket.created_at', 'asc')
            ->get()
            ->map(function ($value, $key) {
                $value->subtotal = round($value->total / (1 + system_iva() / 100), 2);
                $value->iva = round($value->total - $value->subtotal, 2);
                return (array)$value;
            })->toArray();

        $this->concepto['precio_unitario'] = $this->subtotal_facturar;
    }

    public function addConcepto()
    {
        $data = $this->validate([
            'concepto.id' => ['nullable'],
            'concepto.cantidad' => ['required'],
            'concepto.clave_prod_serv_id' => ['required', 'exists:tb_clave_prod_servs,id'],
            'concepto.clave_unidad_id' => ['required', 'exists:tb_clave_unidades,id'],
            'concepto.objeto_impuesto_id' => ['required', 'exists:tb_objetos_impuesto,id'],
            'concepto.descripcion' => 'nullable',
            'concepto.precio_unitario' => ['required', 'numeric', 'gt:0']
        ], [
            'concepto.cantidad.required' => 'Campo requerido',
            'concepto.clave_prod_serv_id.required' => 'Campo requerido',
            'concepto.clave_prod_serv_id.exists' => 'Clave no encontrada',
            'concepto.clave_unidad_id.required' => 'Campo requerido',
            'concepto.clave_unidad_id.exists' => 'Clave no encontrada',
            'concepto.objeto_impuesto_id.required' => 'Campo requerido',
            'concepto.objeto_impuesto_id.exists' => 'Objeto no encontrado',
            'concepto.precio_unitario.required' => 'Campo requerido',
            'concepto.precio_unitario.gt' => 'Entre un valor superior',
        ]);

        $data['concepto']['clave_prod_serv'] = DB::table('tb_clave_prod_servs')->selectRaw("CONCAT_WS(' | ', codigo, nombre) as nombre")->where('id', $data['concepto']['clave_prod_serv_id'])->get()->first()->nombre;
        $data['concepto']['clave_unidad'] = DB::table('tb_clave_unidades')->selectRaw("CONCAT_WS(' | ', codigo, descripcion) as nombre")->where('id', $data['concepto']['clave_unidad_id'])->get()->first()->nombre;
        $data['concepto']['objeto_impuesto'] = DB::table('tb_objetos_impuesto')->selectRaw("CONCAT_WS(' | ', clave, descripcion) as nombre")->where('id', $data['concepto']['objeto_impuesto_id'])->get()->first()->nombre;

        $this->factura_conceptos[] = $data['concepto'];

        $this->concepto = [
            'id' => null,
            'cantidad' => 1,
            'precio_unitario' => '',
            'descripcion' => '',
            'clave_prod_serv_id' => null,
            'clave_unidad_id' => null,
            'objeto_impuesto_id' => null
        ];
    }

    public function mostrarModalEliminarConcepto($index)
    {
        $this->index_concepto_activo = $index;
        $this->modalDeleteConceptoClass = 'show';
    }

    public function eliminarConcepto()
    {
        array_splice($this->factura_conceptos, $this->index_concepto_activo, 1);
        $this->factura_conceptos = array_values($this->factura_conceptos);
        $this->modalDeleteConceptoClass = '';
        $this->emit('show-toast', 'Concepto eliminado', 'success');
    }

    public function goToList()
    {
        return redirect()->route('pre-facturas.index');
    }

    public function timbrar()
    {
        $folio_interno = $this->factura->serie->descripcion . '-' . Factura::internalSheetGenerator($this->factura->serie_id, modo_facturacion() == 1);
        $facturador = new Facturador($this->factura->propietario);
        $res = $facturador->timbrarFactura($this->factura->id, $folio_interno);
        if ($res['success']) {
            $this->emit('show-toast', "Factura timbrada satisfactoriamente.");
        } else {
            $this->emit('show-toast', pretty_message($res['message'], 'danger'), 'danger');
        }
    }
}
