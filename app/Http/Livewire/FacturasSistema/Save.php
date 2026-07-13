<?php

namespace App\Http\Livewire\FacturasSistema;

use App\Models\ClaveProdServ;
use App\Models\ClaveUnidad;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\FacturaConcepto;
use App\Models\Moneda;
use App\Models\ObjetoImpuesto;
use App\Models\Suscripcion;
use App\Models\TipoRelacionFactura;
use App\Rules\DataClientRule;
use App\Rules\FacturaConeptosRule;
use App\Services\Timbrado\Facturador;
use App\Traits\DebuggingLivewire;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Livewire\Component;

class Save extends Component
{
    use DebuggingLivewire;
    // ✅ ID ÚNICO - esto nunca cambia
    public $factura_id;
    public $scope;

    // ✅ DATOS SIMPLES - siempre serializables
    public $folio_interno = '';
    public $lugar_expedicion = '';
    public $fecha_pago = '';
    public $fecha_emision = '';
    public $fecha_emision_str = '';
    public $comentarios = '';
    public $cantidad_letras = '';
    public $estado = 'CAPTURADA';
    public $moneda = 'MXN';
    public $uuid = '';
    public $anio = '';
    public $porciento_iva = 0;
    public $tipo_cambio = 0;

    // ✅ IDs FORÁNEOS - siempre serializables
    public $cfdi_id;
    public $metodo_pago_id;
    public $forma_pago_id;
    public $cliente_id;
    public $serie_id;
    public $periodicidad_id;
    public $mes_id;
    public $tipo_comprobante_id;
    public $tipo_relacion_factura_id = '';

    // ✅ BOOLEANOS
    public $con_facturas_relacionadas = false;
    // ✅ ARRAYS PLANOS - cuidado con objetos dentro
    public $factura_conceptos = [];
    public $codigo_postal = '';
    public $facturasTimbradas = [];
    public $suscripciones = [];
    public $iframeContainerClass = '';
    public $iframeSrc = '';

    // ❌ NUNCA PÚBLICOS - Propiedades que causan problemas
    // ANTES: public Factura $factura; ← ¡ELIMINADA!

    protected $listeners = [
        '$refresh',
        'concepto-creado' => 'addConcepto',
        'concepto-modificado' => 'updateConcepto',
        'consecutivo-factura-generado' => 'timbrarFactura'
    ];

    // ✅ Propiedades que NO se serializan
    // protected $queryString = ['factura_id'];

    public function mount($id = null)
    {
        if (config('app.debug')) {
            $this->debugSerializableProperties();
        }

        $this->factura_id = $id;
        $this->initializeComponent();
    }

    /**
     * Se ejecuta DESPUÉS de la hidratación (muy importante)
     */
    public function hydrate()
    {
        // Aquí el componente ya fue deserializado
        // Si hay error aquí, es un problema de serialización
        try {
            $this->dispatchBrowserEvent('reApplySelect2');
        } catch (\Exception $e) {
            Log::error('Error en hydrate', [
                'message' => $e->getMessage(),
                'factura_id' => $this->factura_id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Obtiene el modelo FRESCO (nunca lo almacena)
     */
    private function getFactura()
    {
        if ($this->factura_id) {
            return Factura::find($this->factura_id);
        }
        return new Factura();
    }

    /**
     * Inicializa TODAS las propiedades públicas desde cero
     */
    private function initializeComponent()
    {
        $factura = $this->getFactura();

        // Cargar datos estáticos (tabla)
        // $this->tipoRelacionesFacturas = TipoRelacionFactura::all()
        //     ->map(fn($t) => [
        //         'value' => $t->id,
        //         'label' => $t->label,
        //         'id' => $t->id,
        //         'codigo' => $t->codigo
        //     ])
        //     ->toArray();

        // $this->anios = range(date('Y'), date('Y') - 1);

        // Inicializar desde la factura
        if ($factura->exists) {
            $this->lugar_expedicion = $factura->lugar_expedicion ? (string)$factura->lugar_expedicion : (string)get_owner()->direccion_fiscal->codigo_postal;
            $this->folio_interno = (string)$factura->folio_interno;
            $this->fecha_emision = (string)$factura->fecha_emision_en;
            $this->fecha_emision_str = (string)$factura->fecha_emision_str;
            $this->comentarios = (string)$factura->comentarios;
            $this->cantidad_letras = (string)$factura->cantidad_letras;
            $this->moneda = (string)$factura->moneda;
            $this->porciento_iva = (float)$factura->porciento_iva;

            $this->anio = (string)$factura->anio;
            $this->tipo_cambio = (float)$factura->tipo_cambio;
            $this->tipo_relacion_factura_id = (string)$factura->tipo_relacion_factura_id;
            $this->con_facturas_relacionadas = (bool)$factura->con_facturas_relacionadas;

            $this->forma_pago_id = $factura->forma_pago ? (int)$factura->forma_pago_id : null;
            $this->cliente_id = $factura->cliente ? (int)$factura->cliente_id : null;
            $this->serie_id = $factura->serie ? (int)$factura->serie_id : null;
            $this->periodicidad_id = $factura->periodicidad ? (int)$factura->periodicidad_id : null;
            $this->mes_id = $factura->mes ? (int)$factura->mes_id : null;
            $this->cfdi_id = (int)$factura->cfdi_id;
            $this->metodo_pago_id = (int)$factura->metodo_pago_id;
            $this->tipo_comprobante_id = (int)$factura->tipo_comprobante_id;
        } else {
            $this->lugar_expedicion = (string)get_owner()->direccion_fiscal->codigo_postal;
            $this->fecha_emision = now()->format('Y-m-d H:i:s');
            $this->fecha_emision_str = now()->format('d/m/Y H:i');
            $this->estado = 'CAPTURADA';
            $this->moneda = 'MXN';
            $this->porciento_iva = (float)system_iva();
            $this->tipo_cambio = (float)get_tipo_cambio_sistema()->tasa;
        }

        // Cargar opciones de select2
        // $this->loadSelectOptions();

        // Cargar datos iniciales
        $this->loadInitialData();
    }

    public function updated()
    {
        $this->dispatchBrowserEvent('reApplySelect2');
        $this->setSelect2Values();
        $this->dispatchBrowserEvent('viewDataChanged');
    }

    public function updatedClienteId($value)
    {
        $this->suscripciones = [];
        $this->facturasTimbradas = [];

        foreach ($this->factura_conceptos as $index => $element) {
            if ($element['suscripcion'] != null) {
                $this->comentarios = Str::replace("Suscripción " . $element['suscripcion']['paquete'] . ", ", '', $this->comentarios);
                $this->comentarios = Str::replace("Suscripción " . $element['suscripcion']['paquete'], '', $this->comentarios);
                array_splice($this->factura_conceptos, $index, 1);
            }
        }

        if ($value) {
            $this->suscripciones = Cliente::find($value)->suscripciones()->get()->map(function (Suscripcion $value) {
                return [
                    'id' => $value->id,
                    'seleccionada' => false,
                    'paquete' => $value->paquete ? $value->paquete->nombre : 'Custom',
                    'estado' => $value->estado,
                    'monto_facturar' => $value->pendiente_facturar
                ];
            })->toArray();

            $this->facturasTimbradas = $this->loadFacturasTimbradasCliente($value);
        }
    }

    public function updatedMoneda($value)
    {
        if ($value === 'MXN') {
            foreach ($this->factura_conceptos as $index => $element) {
                $this->factura_conceptos[$index]['precio_unitario'] = round(
                    $element['precio_unitario'] * $this->tipo_cambio,
                    2
                );
            }
        } elseif ($value === 'USD') {
            foreach ($this->factura_conceptos as $index => $element) {
                $this->factura_conceptos[$index]['precio_unitario'] = round(
                    $element['precio_unitario'] / $this->tipo_cambio,
                    2
                );
            }
        }
        $this->emit('$refresh');
    }

    // ✅ COMPUTED PROPERTIES (no se serializan)
    public function getSubtotalProperty()
    {
        $total = 0;
        for ($i = 0; $i < count($this->factura_conceptos); $i++) {
            $total += $this->factura_conceptos[$i]['cantidad'] * $this->factura_conceptos[$i]['precio_unitario'];
        }
        return round($total, 2);
    }

    public function getIvaProperty()
    {
        return round($this->subtotal * $this->porciento_iva / 100, 2);
    }

    public function getTotalProperty()
    {
        $total = $this->iva + $this->subtotal;
        $this->cantidad_letras = convertir_numero_a_letras($total, $this->moneda);
        return $total;
    }

    public function getFacturaConceptosSelectedCantidadProperty()
    {
        $cantidad = 0;
        for ($i = 0; $i < count($this->suscripciones); $i++) {
            if ($this->suscripciones[$i]['seleccionada']) {
                $cantidad++;
            }
        }
        return $cantidad;
    }

    public function getImporteCobrarProperty()
    {
        $importe_cobrar = 0;

        foreach ($this->suscripciones as $element) {
            if ($element['seleccionada']) {
                $importe_cobrar += $element['total'];
            }
        }
        return round($importe_cobrar, 2);
    }

    public function getSuscripcionesSeleccionadasProperty()
    {
        $seleccionadas = [];
        foreach ($this->suscripciones as $index => $sub) {
            if ($sub['seleccionada']) {
                $seleccionadas[] = $sub;
            }
        }
        return $seleccionadas;
    }

    public function getFacturasRelacionadasSeleccionadasProperty()
    {
        $seleccionadas = [];
        foreach ($this->facturasTimbradas as $index => $facturasTimbrada) {
            if ($facturasTimbrada['seleccionada']) {
                $seleccionadas[] = $facturasTimbrada;
            }
        }
        return $seleccionadas;
    }

    public function getClientIsSelectedProperty()
    {
        return $this->cliente_id > 0;
    }

    public function getClientSelectedProperty()
    {
        $cliente = Cliente::find($this->cliente_id)?->toArray();
        return $cliente ? Cliente::decryptInfo($cliente) : null;
    }

    public function getRazonSocialReceptorProperty()
    {
        return $this->client_selected ? $this->client_selected['razon_social'] : '';
    }

    public function getRfcReceptorProperty()
    {
        return $this->client_selected ? $this->client_selected['rfc'] : '';
    }

    public function getCodigoPostalReceptorProperty()
    {
        return $this->client_selected ? $this->client_selected['codigo_postal'] : '';
    }

    public function getClientIsPublicoGeneralProperty()
    {
        return $this->cliente_id == 57;
    }

    public function getFormWithErrorsProperty()
    {
        $withErrors = $this->getErrorBag()->isNotEmpty();
        if ($withErrors)
            $this->emit('show-toast', __('site.invoices.save_invoice.errors_in_form'), 'danger');
        return $withErrors;
    }

    public function render()
    {
        $cleanArray = function ($query) {
            return $query->get()->map(function ($item) {
                return (array) $item; // Convierte el objeto stdClass a array plano rápidamente
            })->toArray();
        };
        return view('livewire.facturas-sistema.save', [
            'factura' => $this->getFactura(),
            'monedas' => Moneda::pluck('acronimo')->toArray(),
            'series' => $cleanArray(DB::table('tb_series')->where('activo', 1)->select('id as value', 'descripcion as label')),
            'metodosPago' => $cleanArray(DB::table('tb_metodo_pagos')->where('activo', 1)->select('id as value', DB::raw("CONCAT_WS(' | ', codigo, descripcion) as label"))),
            'formasPago' => $cleanArray(DB::table('tb_forma_pagos')->where('activo', 1)->select('id as value', DB::raw("CONCAT_WS(' | ', codigo, descripcion) as label"))),
            'usosCfdi' => $cleanArray(DB::table('tb_cfdis')->where('activo', 1)->select('id as value', DB::raw("CONCAT_WS(' | ', codigo, descripcion) as label"))),
            'tiposComprobante' => $cleanArray(DB::table('tb_tipo_comprobantes')->where('activo', 1)->select('id as value', DB::raw("CONCAT_WS(' | ', codigo, descripcion) as label"))),
            'periodicidades' => $cleanArray(DB::table('tb_periodicidades_factura')->select('id as value', DB::raw("CONCAT_WS(' | ', clave, descripcion) as label"))),
            'meses' => $cleanArray(DB::table('tb_meses')->select('id as value', DB::raw("CONCAT_WS(' | ', clave, descripcion) as label"))),
            'anios' => range(date('Y'), date('Y') - 1),
            'tipoRelacionesFacturas' => TipoRelacionFactura::all()
                ->map(fn($t) => [
                    'value' => $t->id,
                    'label' => $t->label,
                    'id' => $t->id,
                    'codigo' => $t->codigo
                ])
                ->toArray()
        ]);
    }

    public function loadInitialData()
    {
        $this->setSelect2Values();

        if ($this->factura_id) {
            $this->loadSuscripciones();
            $this->loadFacturasConceptos();

            if ($this->cliente_id) {
                $this->facturasTimbradas = $this->loadFacturasTimbradasCliente($this->cliente_id);
            }
        }
    }

    public function setSelect2Values()
    {
        if ($this->cliente_id) {
            $cliente = Cliente::decryptInfo(Cliente::find($this->cliente_id));
            $this->dispatchBrowserEvent('set-data-cliente_id', [
                'data' => [$cliente->only('id', 'text')],
                'term' => '',
                'value' => $this->cliente_id
            ]);
        }

        if (!$this->cfdi_id) {
            $this->cfdi_id = 3;
        }
        if (!$this->metodo_pago_id) {
            $this->metodo_pago_id = 2;
        }
        if (!$this->tipo_comprobante_id) {
            $this->tipo_comprobante_id = 1;
        }
    }

    public function loadFacturasTimbradasCliente($cliente_id)
    {
        $facturas = DB::table('tb_facturas as factura')
            ->select(
                'factura.id as id',
                'factura.folio_interno as folio_interno',
                'factura.estado',
                'factura.es_nota_credito',
                'factura.es_complemento',
                'factura.tipo_relacion_factura_id as tipo_relacion_id',
                DB::raw("DATE_FORMAT(factura.fecha_emision,'%d/%m/%Y %H:%i') as fecha_emision_str"),
                DB::raw("0 as seleccionada"),
                DB::raw("factura.subtotal as subtotal"),
                DB::raw("factura.total as total")
            )->where('factura.cliente_id', $cliente_id)
            ->where('factura.propietario_id', get_system_owner()->id)
            ->whereIn('factura.estado', ['TIMBRADA', 'COBRADA'])
            ->get();

        return $facturas->map(function ($value, $key) {
            if ($this->getFactura()->exists && in_array($value->id, $this->getFactura()->facturas_relacionadas()->pluck('id')->toArray()))
                $value->seleccionada = 1;
            return (array)$value;
        })->toArray();
    }

    public function saveFactura($timbrando = false)
    {
        if ($this->con_facturas_relacionadas && !$this->tipo_relacion_factura_id) {
            $this->emit('show-toast', 'Debe seleccionar el Tipo de Relación.', 'warning');
            return;
        }

        $rules = [
            'lugar_expedicion' => ['required'],
            'con_facturas_relacionadas' => ['nullable'],
            'tipo_relacion_factura_id' => ['nullable'],
            'porciento_iva' => ['nullable'],
            'estado' => ['nullable'],
            'comentarios' => ['nullable'],
            'cantidad_letras' => ['nullable'],
            'fecha_emision' => ['required'],
            'cliente_id' => ['required', 'exists:tb_clientes,id', new DataClientRule()],
            'cfdi_id' => ['required', 'exists:tb_cfdis,id'],
            'serie_id' => ['required', 'exists:tb_series,id'],
            'forma_pago_id' => ['required_if:es_nomina,false', 'exists:tb_forma_pagos,id'],
            'metodo_pago_id' => ['required', 'exists:tb_metodo_pagos,id'],
            'tipo_comprobante_id' => ['required_if:es_nomina,false', 'exists:tb_tipo_comprobantes,id'],
            'moneda' => ['required_if:es_nomina,false'],
            'tipo_cambio' => ['required_if:es_nomina,false'],
            'periodicidad_id' => ['required_if:cliente_id,57'],
            'mes_id' => ['required_if:cliente_id,57'],
            'anio' => ['required_if:cliente_id,57'],
            'factura_conceptos' => ['nullable', 'array', 'min:1'],
            'suscripciones' => ['nullable', 'array'],
            'factura_conceptos.*.cantidad' => ['required', 'numeric', 'gt:0'],
            'factura_conceptos.*.precio_unitario' => ['required', 'numeric', 'gt:0'],
            'factura_conceptos.*.clave_prod_serv_id' => ['required', 'exists:tb_clave_prod_servs,id'],
            'factura_conceptos.*.objeto_impuesto_id' => ['required', 'exists:tb_objetos_impuesto,id'],
            'factura_conceptos.*.clave_unidad_id' => ['required', 'exists:tb_clave_unidades,id'],
        ];

        $messages = [
            'cliente_id.lugar_expedicion' => 'Campo requerido.',
            'cliente_id.required_if' => 'Campo requerido.',
            'cfdi_id.required' => 'Campo requerido.',
            'serie_id.required' => 'Campo requerido.',
            'forma_pago_id.required_if' => 'Campo requerido.',
            'metodo_pago_id.required' => 'Campo requerido.',
            'tipo_comprobante_id.required_if' => 'Campo requerido.',
            'moneda.required_if' => 'Campo requerido.',
            'tipo_cambio.required_if' => 'Campo requerido.',
            'periodicidad_id.required_if' => 'Campo requerido.',
            'mes_id.required_if' => 'Campo requerido.',
            'anio.required_if' => 'Campo requerido.',
            'factura_conceptos.*.cantidad.required' => 'Campo requerido.',
            'factura_conceptos.*.cantidad.gt' => 'La cantidad debe ser mayor a 0.',
            'factura_conceptos.*.precio_unitario.required' => 'Campo requerido.',
            'factura_conceptos.*.precio_unitario.gt' => 'El precio debe ser mayor a 0.',
            'factura_conceptos.*.clave_prod_serv_id.required' => 'Campo requerido.',
            'factura_conceptos.*.clave_prod_serv_id.exists' => 'Clave no encontrada.',
            'factura_conceptos.*.objeto_impuesto_id.required' => 'Campo requerido.',
            'factura_conceptos.*.objeto_impuesto_id.exists' => 'Objeto no encontrado.',
            'factura_conceptos.*.clave_unidad_id.required' => 'Campo requerido.',
            'factura_conceptos.*.clave_unidad_id.exists' => 'Clave no encontrada.',
        ];

        $data = $this->validate(
            $rules,
            // $messages
        );
        $data['subtotal'] = $this->subtotal;
        $data['iva'] = $this->iva;
        $data['total'] = $this->total;
        $data['del_sistema'] = 1;
        $data['propietario_type'] = Cliente::class;
        $data['tipo_relacion_factura_id'] = $data['tipo_relacion_factura_id'] ?: null;
        $data['user_id'] = user()->id;

        $factura = $this->getFactura();

        if (!$factura->id) {
            $data['propietario_id'] = get_system_owner()->id;
        }

        $factura->fill(Arr::except($data, ['factura_conceptos', 'suscripciones']))->save();

        // Actualiza el ID si fue creada
        if ($factura->wasRecentlyCreated) {
            $this->factura_id = $factura->id;
        }

        $invoices_concepts_ids = [];
        foreach ($this->factura_conceptos as $concept) {
            if (isset($concept['id']) && $concept['id'] > 0) {
                $i_c = FacturaConcepto::find($concept['id']);
                $i_c->fill([
                    'cantidad' => $concept['cantidad'],
                    'precio_unitario' => $concept['precio_unitario'],
                    'descripcion' => $concept['descripcion'],
                    'clave_unidad_id' => $concept['clave_unidad_id'],
                    'clave_prod_serv_id' => $concept['clave_prod_serv_id'],
                    'objeto_impuesto_id' => $concept['objeto_impuesto_id'],
                    'suscripcion_id' => isset($concept['suscripcion']) && $concept['suscripcion'] ? $concept['suscripcion']['id'] : null,
                ])->save();
                $invoices_concepts_ids[] = $i_c->id;
            } else {
                $i_c = $factura->factura_conceptos()->create([
                    'cantidad' => $concept['cantidad'],
                    'precio_unitario' => $concept['precio_unitario'],
                    'descripcion' => $concept['descripcion'],
                    'clave_unidad_id' => $concept['clave_unidad_id'],
                    'clave_prod_serv_id' => $concept['clave_prod_serv_id'],
                    'objeto_impuesto_id' => $concept['objeto_impuesto_id'],
                    'suscripcion_id' => isset($concept['suscripcion']) && $concept['suscripcion'] ? $concept['suscripcion']['id'] : null,
                ]);
                $invoices_concepts_ids[] = $i_c->id;
            }
        }

        $factura->factura_conceptos()->whereNotIn('id', $invoices_concepts_ids)->delete();

        if ($data['con_facturas_relacionadas'] && $data['tipo_relacion_factura_id']) {
            $facturas_rels_ids = [];
            foreach ($this->facturas_relacionadas_seleccionadas as $fact) {
                $facturas_rels_ids[] = $fact['id'];
            }
            $factura->facturas_relacionadas()->sync($facturas_rels_ids);
        }

        activity('Facturas del Sistema')
            ->on($factura)
            ->causedBy(user()->id)
            ->withProperties($factura->toArray())
            ->log(($factura->wasRecentlyCreated ? 'Creada' : 'Modificada') . " Factura con id: " . $factura->id);

        $this->emit('show-toast', 'Pre-Factura guardada.', 'success');
        $this->dispatchBrowserEvent('resetViewDataChange');

        if (!$timbrando) {
            $this->redirect(route('admin.pre-facturas.save', $factura->id));
        }
    }
    public function loadSuscripciones()
    {
        if ($this->cliente_id) {
            $this->suscripciones = Cliente::find($this->cliente_id)->suscripciones()->get()->map(function (Suscripcion $sub) {
                $concepto = FacturaConcepto::where('factura_id',  $this->getFactura()->id)->where('suscripcion_id', $sub->id)->first();
                return [
                    'id' => $sub->id,
                    'seleccionada' => $concepto != null,
                    'paquete' => $sub->paquete ? $sub->paquete->nombre : 'Custom',
                    'estado' => $sub->estado,
                    'monto_facturar' => $concepto ? ($sub->total - $sub->factura_conceptos()->where('id', '!=',  $concepto->id)->sum(DB::raw('cantidad * precio_unitario'))) : $sub->pendiente_facturar
                ];
            });
        } else {
            $this->suscripciones = [];
        }
    }
    public function loadFacturasConceptos()
    {
        $factura = $this->getFactura();
        if ($factura->exists) {
            $this->factura_conceptos = DB::table('tb_factura_conceptos')
                ->select(
                    'id',
                    'cantidad',
                    'precio_unitario',
                    'descripcion',
                    'clave_unidad_id',
                    DB::raw("'' as clave_unidad"),
                    'clave_prod_serv_id',
                    DB::raw("'' as clave_prod_serv"),
                    'objeto_impuesto_id',
                    DB::raw("'' as objeto_impuesto"),
                    'suscripcion_id',
                    DB::raw("'' as suscripcion"),
                )
                ->where('factura_id', $factura->id)
                ->get()->map(function ($element) {
                    $element->clave_unidad = (array)DB::table('tb_clave_unidades')
                        ->select('id as value', DB::raw("CONCAT_WS(' | ', codigo, descripcion) as label"), 'codigo')
                        ->where('id', $element->clave_unidad_id)
                        ->first();
                    $element->clave_prod_serv = (array)DB::table('tb_clave_prod_servs')
                        ->select('id as value', DB::raw("CONCAT_WS(' | ', codigo, nombre) as label"), 'codigo')
                        ->where('id', $element->clave_prod_serv_id)
                        ->first();
                    $element->objeto_impuesto = (array)DB::table('tb_objetos_impuesto')
                        ->select('id as value', DB::raw("CONCAT_WS(' | ', clave, descripcion) as label"), DB::raw("CONCAT_WS(' | ', clave, descripcion) as nombre"))
                        ->where('id', $element->objeto_impuesto_id)
                        ->first();
                    $element->suscripcion = (array)DB::table('tb_suscripciones as sub')
                        ->select(
                            'sub.id',
                            'paquete.nombre as paquete',
                            'sub.estado',
                            DB::raw("(SELECT SUM(fc.precio_unitario * fc.cantidad) from tb_factura_conceptos as fc where fc.suscripcion_id = sub.id) as monto_facturar"),
                            'sub.paquete_id'
                        )
                        ->leftJoin('tb_paquetes as paquete', 'paquete.id', 'sub.paquete_id')
                        ->where('sub.id', $element->suscripcion_id)->get()->map(function ($value, $key) {
                            return (array)$value;
                        })->first();
                    return (array)$element;
                })->toArray();
        } else {
            $this->factura_conceptos = [];
        }
    }

    public function checkSuscripcion($index)
    {
        $suscripcion = $this->suscripciones[$index];
        if (!$suscripcion['seleccionada']) {
            $this->seleccionarSuscripcion($index);
        } else {
            $this->suscripciones[$index]['seleccionada'] = false;
            $this->comentarios = Str::replace("Suscripción " . $suscripcion['paquete'] . ", ", '', $this->comentarios);
            $this->comentarios = Str::replace("Suscripción " . $suscripcion['paquete'], '', $this->comentarios);
            $this->factura_conceptos = array_values(Arr::where($this->factura_conceptos, function ($element) use ($suscripcion) {
                return !$element['suscripcion'] || $element['suscripcion']['id'] != $suscripcion['id'];
            }));
        }
        $this->cantidad_letras = convertir_numero_a_letras($this->total, $this->moneda);
    }

    public function seleccionarSuscripcion($index)
    {
        $suscripcion = $this->suscripciones[$index];
        $this->suscripciones[$index]['seleccionada'] = true;

        $this->comentarios .= "Suscripción " . $suscripcion['paquete'] . ", ";

        $sub = Suscripcion::find($this->suscripciones[$index]['id']);
        $descripcion = "Subscripción al paquete {$suscripcion['paquete']}. Cant: módulos: {$sub->modulos()->count()}. Cant. de sucursales: $sub->cant_sucursales. Cant. de terminales: $sub->cant_terminales. Cant. de usuarios: $sub->cant_usuarios";

        $concepto = [
            'id' => '',
            'cantidad' => 1,
            'descripcion' => $descripcion,
            'precio_unitario' => $this->suscripciones[$index]['monto_facturar'],
            'clave_unidad_id' => null,
            'clave_unidad' => null,
            'clave_prod_serv_id' => null,
            'clave_prod_serv' => null,
            'objeto_impuesto_id' => null,
            'objeto_impuesto' => null,
            'suscripcion_id' => $this->suscripciones[$index]['id'],
            'suscripcion' => $this->suscripciones[$index]
        ];
        $this->factura_conceptos[] = $concepto;
    }

    public function checkFacturaTimbrada($index)
    {
        $this->facturasTimbradas[$index]['seleccionada'] = !$this->facturasTimbradas[$index]['seleccionada'];
    }
    public function showConceptoFacturaModal($index = null)
    {
        if ($index === null) {
            $this->emitTo('facturas-sistema.save-factura-concepto', 'nuevo-concepto-factura', 'facturas-sistema.save');
        } else {
            $this->emitTo('facturas-sistema.save-factura-concepto', 'editar-concepto-factura', $index, $this->factura_conceptos[$index], 'facturas-sistema.save');
        }
    }

    public function eliminarConceptoFactura($index)
    {
        if ($this->factura_conceptos[$index]['suscripcion'] != null) {
            foreach ($this->suscripciones as $i => $element) {
                if ($element['id'] == $this->factura_conceptos[$index]['suscripcion']['id']) {
                    $this->suscripciones[$i]['seleccionada'] = false;
                    $this->comentarios = Str::replace("Suscripción " . $element['paquete'] . ", ", '', $this->comentarios);
                    $this->comentarios = Str::replace("Suscripción " . $element['paquete'], '', $this->comentarios);
                    $this->emit('unselect-suscripcion', $element['id']);
                }
            }
        }
        array_splice($this->factura_conceptos, $index, 1);
    }

    public function addConcepto($data)
    {
        $data['id'] = null;
        $data['clave_unidad'] = ClaveUnidad::find($data['clave_unidad_id'])->only('value', 'label', 'codigo');
        $data['clave_prod_serv'] = ClaveProdServ::find($data['clave_prod_serv_id'])->only('value', 'label', 'codigo');
        $data['objeto_impuesto'] = ObjetoImpuesto::find($data['objeto_impuesto_id'])->only('value', 'label', 'nombre');
        $data['suscripcion'] = null;
        $this->factura_conceptos[] = $data;
    }

    public function updateConcepto($index, $data)
    {
        $this->factura_conceptos[$index]['cantidad'] = $data['cantidad'];
        $this->factura_conceptos[$index]['precio_unitario'] = $data['precio_unitario'];
        $this->factura_conceptos[$index]['descripcion'] = $data['descripcion'];
        if ($this->factura_conceptos[$index]['objeto_impuesto_id'] != $data['objeto_impuesto_id']) {
            $this->factura_conceptos[$index]['objeto_impuesto_id'] = $data['objeto_impuesto_id'];
            $this->factura_conceptos[$index]['objeto_impuesto'] = ObjetoImpuesto::find($data['objeto_impuesto_id'])->only('value', 'label', 'nombre');
        }
        if ($this->factura_conceptos[$index]['clave_prod_serv_id'] != $data['clave_prod_serv_id']) {
            $this->factura_conceptos[$index]['clave_prod_serv_id'] = $data['clave_prod_serv_id'];
            $this->factura_conceptos[$index]['clave_prod_serv'] = ClaveProdServ::find($data['clave_prod_serv_id'])->only('value', 'label', 'codigo');
        }
        if ($this->factura_conceptos[$index]['clave_unidad_id'] != $data['clave_unidad_id']) {
            $this->factura_conceptos[$index]['clave_unidad_id'] = $data['clave_unidad_id'];
            $this->factura_conceptos[$index]['clave_unidad'] = ClaveUnidad::find($data['clave_unidad_id'])->only('value', 'label', 'codigo');
        }
    }

    public function showPdf()
    {
        $name = Factura::generatePdf($this->factura_id, true);
        $this->iframeSrc = Request::root() . "/$name?" . time();
        $this->iframeContainerClass = 'show';
    }

    public function timbrarFactura($id, $consecutivo = null)
    {
        if (!$consecutivo) {
            $this->emit('nuevo-consecutivo-factura', $id, 'facturas-sistema.save');
            return;
        }
        $this->saveFactura(true);
        $facturador = new Facturador(get_system_owner());
        $res = $facturador->timbrarFactura($id, $consecutivo);
        $this->emit('show-toast', $res['message'], $res['success'] ? 'success' : 'danger');
        if ($res['success'])
            $this->redirect(route('admin.pre-facturas.index'));
    }
}
