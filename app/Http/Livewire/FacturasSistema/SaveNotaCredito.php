<?php

namespace App\Http\Livewire\FacturasSistema;

use App\Http\Controllers\Helpers\Helper;
use App\Models\Cfdi;
use App\Models\ClaveProdServ;
use App\Models\ClaveUnidad;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\FormaPago;
use App\Models\Moneda;
use App\Models\Serie;
use App\Rules\DataClientRule;
use App\Rules\FacturaConeptosRule;
use App\Services\Timbrado\Facturador;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use function Laravel\Prompts\alert;

class SaveNotaCredito extends Component
{
    public $scope;
    public Factura $notaCredito;
    public $folio_interno;
    public $fecha_emision;
    public $fecha_emision_str;
    public $comentarios;
    public $cantidad_letras;
    public $estado;
    public $moneda;
    public $tipo_cambio;
    public $cfdi_id;
    public $forma_pago_id;
    public $metodo_pago_id;
    public $cliente_id;
    public $serie_id;
    public $porciento_iva;
    public $factura_conceptos = [];
    public $facturasFiscalesAll = [];
    public $facturasRelacionadasAll = [];

    public $iframeContainerClass = '';
    public $iframeSrc = '';
    public $asociar_facturas = false;


    protected $listeners = ['$refresh', 'consecutivo-factura-generado' => 'timbrarFactura'];

    public function mount($id = null)
    {
        if ($id) {
            $this->notaCredito = Factura::find($id);
        } else {
            $this->notaCredito = new Factura();
        }

        $this->folio_interno = $this->notaCredito->exists ? $this->notaCredito->folio_interno : '';
        $this->fecha_emision = $this->notaCredito->exists ? $this->notaCredito->fecha_emision_en : now()->format('Y-m-d H:i:s');
        $this->fecha_emision_str = $this->notaCredito->exists ? $this->notaCredito->fecha_emision_str : now()->format('d/m/Y H:i');
        $this->comentarios = $this->notaCredito->exists ? $this->notaCredito->comentarios : '';
        $this->cantidad_letras = $this->notaCredito->exists ? $this->notaCredito->cantidad_letras : '';
        $this->estado = $this->notaCredito->exists ? $this->notaCredito->estado : 'CAPTURADA';
        $this->moneda = $this->notaCredito->exists ? $this->notaCredito->moneda : 'MXN';
        $this->tipo_cambio = $this->notaCredito->exists && $this->notaCredito->tipo_cambio ? $this->notaCredito->tipo_cambio : get_tipo_cambio_sistema()->tasa;
        $this->porciento_iva = $this->notaCredito->exists ? $this->notaCredito->porciento_iva : system_iva();
        if ($this->notaCredito->exists) {
            $this->cfdi_id = $this->notaCredito->cfdi_id;
            $this->forma_pago_id = $this->notaCredito->forma_pago_id;
            $this->metodo_pago_id = $this->notaCredito->metodo_pago_id;
            $this->cliente_id = $this->notaCredito->cliente_id;
            $this->serie_id = $this->notaCredito->serie_id;
            $this->factura_conceptos = $this->notaCredito->factura_conceptos()
                ->with(['clave_unidad:id,codigo,descripcion', 'clave_prod_serv:id,nombre'])
                ->get()->map->only(['id', 'cantidad', 'clave_unidad_id', 'clave_prod_serv_id', 'clave_unidad', 'clave_prod_serv', 'descripcion', 'precio_unitario'])
                ->toArray();
        }
    }

    public function hydrate()
    {
        $this->dispatchBrowserEvent('reApplySelect2');
    }

    public function updated()
    {
        $this->cantidad_letras = convertir_numero_a_letras($this->total, $this->moneda);
        $this->dispatchBrowserEvent('reApplySelect2');
    }

    public function updatedClienteId($value)
    {
        $this->facturasFiscalesAll = [];
        $this->facturasRelacionadasAll = [];
        $this->factura_conceptos = [];
        if ($value) {
            $this->facturasFiscalesAll = $this->loadFacturasFiscalesAll($value);
            $this->facturasRelacionadasAll = $this->loadFacturasRelacionadasAll($value);
        }
    }

    public function updatedMoneda($value)
    {
        if (count($this->factura_conceptos) > 0) {
            $subtotal = array_reduce($this->facturas_relacionadas, function ($carry, $item) {
                if ($this->moneda != $item['moneda']) {
                    if ($item['moneda'] == 'MXN') {
                        $carry += round($item['subtotal'] / $item['tipo_cambio'], 2);
                    } else {
                        $carry += round($item['subtotal'] * $item['tipo_cambio'], 2);
                    }
                } else {
                    $carry += $item['subtotal'];
                }
                return $carry;
            });
            $this->factura_conceptos[0]['precio_unitario'] = $subtotal;
        }
        $this->emit('$refresh');
    }

    public function loadInitialData()
    {
        if ($this->notaCredito->exists) {
            if ($this->cliente_id) {
                $this->dispatchBrowserEvent('set-data-cliente_id', ['data' => [Cliente::find($this->cliente_id)->only('id', 'text')], 'term' => '', 'value' => $this->cliente_id]);
            }
            if ($this->serie_id) {
                $this->dispatchBrowserEvent('set-data-serie_id', ['data' => [Serie::find($this->serie_id)->only('id', 'text')], 'term' => '', 'value' => $this->serie_id]);
            }
            if ($this->cfdi_id) {
                $this->dispatchBrowserEvent('set-data-cfdi_id', ['data' => [Cfdi::find($this->cfdi_id)->only('id', 'text')], 'term' => '', 'value' => $this->cfdi_id]);
            }
            if ($this->forma_pago_id) {
                $this->dispatchBrowserEvent('set-data-forma_pago_id', ['data' => [FormaPago::find($this->forma_pago_id)->only('id', 'text')], 'term' => '', 'value' => $this->forma_pago_id]);
            }
            if ($this->cliente_id) {
                $this->facturasFiscalesAll = $this->loadFacturasFiscalesAll($this->cliente_id);
                $this->facturasRelacionadasAll = $this->loadFacturasRelacionadasAll($this->cliente_id);
            }
        } else {
            // $this->serie_id = 6;
            // $this->dispatchBrowserEvent('set-data-serie_id', ['data' => [Serie::find(6)->only('id', 'text')], 'term' => '', 'value' => 6]);
        }
    }

    public function loadFacturasRelacionadasAll($client_id)
    {
        $facturas = Factura::where('cliente_id', $client_id)
            ->whereIn('estado', ['COBRADA'])
            ->select('id', 'folio_interno', 'moneda', 'subtotal', 'tipo_cambio', 'fecha_certificacion', DB::raw('DATE_FORMAT(fecha_certificacion, "%d/%m/%Y %H:%i") as fecha_certificacion_str'))
            ->addSelect(DB::raw('false as seleccionada'))
            ->get();
        if ($this->notaCredito->exists) {
            $facturas->map(function (Factura $fact) {
                $fact->seleccionada = $this->notaCredito->facturas_relacionadas()->where('id', $fact->id)->count() > 0;
            });
        }

        return $facturas->toArray();
    }

    public function loadFacturasFiscalesAll($client_id)
    {
        $facturas = Factura::where('cliente_id', $client_id)
            ->whereIn('estado', ['COBRADA'])
            ->select(
                'id',
                'folio_interno',
                'moneda',
                'estado',
                'subtotal',
                'total',
                'tipo_cambio',
                'fecha_emision',
                DB::raw('DATE_FORMAT(fecha_emision, "%d/%m/%Y %H:%i") as fecha_emision_str'),
                DB::raw('IF(es_complemento = 1, "Complemento", IF(es_nota_credito = 1, "Nota Crédito", "Factura")) as tipo')
            )
            ->addSelect(DB::raw('false as seleccionada'))
            ->get();
        if ($this->notaCredito->exists) {
            $facturas->map(function (Factura $fact) {
                $fact->seleccionada = $this->notaCredito->facturas_fiscales()->where('id', $fact->id)->count() > 0;
            });
        }

        return $facturas->toArray();
    }

    public function render()
    {
        return view('livewire.facturas-sistema.save-nota-credito', [
            'monedas' => Moneda::pluck('acronimo')->toArray()
        ]);
    }

    public function getSubtotalProperty()
    {
        return array_reduce($this->factura_conceptos, function ($carry, $item) {
            $carry += max($item['precio_unitario'], 0);
            return $carry;
        });
    }

    public function getIvaProperty()
    {
        return $this->subtotal * $this->porciento_iva;
    }

    public function getTotalProperty()
    {
        return round($this->subtotal + $this->iva, 2);
    }

    public function getClientIsSelectedProperty()
    {
        return $this->cliente_id > 0;
    }

    public function getClientSelectedProperty()
    {
        return $this->cliente_id > 0 ? Cliente::find($this->cliente_id)->toArray() : null;
    }

    public function getClientIsPublicoGeneralProperty()
    {
        return $this->cliente_id == 57;
    }

    public function getFacturasRelacionadasProperty()
    {
        return array_values(array_filter($this->facturasRelacionadasAll, function ($item) {
            return $item['seleccionada'] == true;
        }));
    }

    public function getFacturasFiscalesProperty()
    {
        return array_values(array_filter($this->facturasFiscalesAll, function ($item) {
            return $item['seleccionada'] == true;
        }));
    }

    public function saveNotaCredito()
    {
        $rules = [
            'estado' => ['nullable'],
            'comentarios' => ['nullable'],
            'cantidad_letras' => ['nullable'],
            'fecha_emision' => ['required'],
            'cliente_id' => ['required', 'exists:tb_empresas,id', new DataClientRule()],
            'cfdi_id' => ['required', 'exists:tb_cfdis,id'],
            'serie_id' => ['required', 'exists:tb_series,id'],
            'forma_pago_id' => ['required', 'exists:tb_forma_pagos,id'],
            'metodo_pago_id' => ['required', 'exists:tb_metodo_pagos,id'],
            'moneda' => ['required'],
            'tipo_cambio' => ['required'],
            'factura_conceptos' => ['array', 'min:1'],
            'factura_conceptos.*.cantidad' => ['required'],
            'factura_conceptos.*.clave_unidad_id' => ['required'],
            'factura_conceptos.*.clave_prod_serv_id' => ['required'],
            'factura_conceptos.*.precio_unitario' => ['required'],
            'factura_conceptos.*.descripcion' => ['required'],
        ];
        $messages = [
            'cliente_id.required' => 'Campo requerido.',
            'cfdi_id.required' => 'Campo requerido.',
            'serie_id.required' => 'Campo requerido.',
            'forma_pago_id.required' => 'Campo requerido.',
            'metodo_pago_id.required' => 'Campo requerido.',
            'moneda.required' => 'Campo requerido.',
            'tipo_cambio.required' => 'Campo requerido.',
            'factura_conceptos.min' => 'Debe seleccionar al menos una factura.',
            'facturas_conceptos.*.cantidad.required' => 'Campo requerido.',
            'facturas_conceptos.*.clave_unidad_id.required' => 'Campo requerido.',
            'facturas_conceptos.*.clave_prod_serv_id.required' => 'Campo requerido.',
            'facturas_conceptos.*.precio_unitario.required' => 'Campo requerido.',
            'facturas_conceptos.*.descripcion.required' => 'Campo requerido.',
        ];
        $data = $this->validate(
            $rules,
            // $messages
        );
        if (!$this->notaCredito->exists) {
            $data['porciento_iva'] = system_iva();
            $data['propietario_id'] = get_system_owner()->id;
            $data['es_nota_credito'] = 1;
            $data['del_sistema'] = 1;
            $data['propietario_type'] = Cliente::class;
            $data['estado'] = 'CAPTURADA';
            $data['tipo_comprobante_id'] = 2;
            $data['tipo_relacion_factura_id'] = 1;
        }

        $data['total'] = $this->total;
        $data['iva'] = $this->iva;
        $data['subtotal'] = $this->subtotal;

        $this->notaCredito->fill(Arr::except($data, ['factura_conceptos']))->save();

        if ($this->notaCredito->wasRecentlyCreated) {
            $this->notaCredito->folio_interno = $this->notaCredito->serie->descripcion . $this->notaCredito->id;
            $this->notaCredito->save();
        }

        if ($this->notaCredito->factura_conceptos()->count() > 0) {
            $this->notaCredito->factura_conceptos()->update($data['factura_conceptos'][0]);
        } else {
            $this->notaCredito->factura_conceptos()->create($data['factura_conceptos'][0]);
        }

        $this->notaCredito->facturas_relacionadas()->sync(array_map(static function ($element) {
            return $element['id'];
        }, $this->facturas_relacionadas));

        if ($this->asociar_facturas) {
            $this->notaCredito->facturas_fiscales()->sync(array_map(static function ($element) {
                return $element['id'];
            }, $this->facturas_fiscales));
        } else {
            $this->notaCredito->facturas_fiscales()->detach();
        }

        if ($this->notaCredito->wasRecentlyCreated)
            $log_detail = __('site.invoices.save_credit_note.save_log_detail_create', ['id' => $this->notaCredito->id]);
        else
            $log_detail = __('site.invoices.save_credit_note.save_log_detail_edit', ['id' => $this->notaCredito->id]);

        activity(__('site.invoices.save_credit_note.save_log_name'))
            ->performedOn($this->notaCredito)
            ->causedBy(auth()->user())
            ->withProperties($this->notaCredito->toArray())
            ->log($log_detail);

        $this->emit('show-toast', __('site.invoices.save_credit_note.credit_note_saved'));
        $this->redirect(route('admin.pre-facturas.index'));
    }

    public function checkFactura($index, $id, $subtotal, $moneda, $tipo_cambio)
    {
        if ($this->facturasRelacionadasAll[$index]['seleccionada']) {
            $this->emit('unselect-factura', $id);
            $this->facturasRelacionadasAll[$index]['seleccionada'] = false;
            if (count($this->facturas_relacionadas) == 0) {
                $this->factura_conceptos = [];
            } else {
                if ($this->moneda != $moneda) {
                    if ($moneda == 'MXN') {
                        $st = round($subtotal / $tipo_cambio, 2);
                    } else {
                        $st = round($subtotal * $tipo_cambio, 2);
                    }
                } else {
                    $st = $subtotal;
                }
                $this->factura_conceptos[0]['precio_unitario'] -= $st;
            }
        } else {
            $this->facturasRelacionadasAll[$index]['seleccionada'] = true;

            if ($this->moneda != $moneda) {
                if ($moneda == 'MXN') {
                    $st = round($subtotal / $tipo_cambio, 2);
                } else {
                    $st = round($subtotal * $tipo_cambio, 2);
                }
            } else {
                $st = $subtotal;
            }

            if (count($this->factura_conceptos) == 0) {
                $clave_unidad = ClaveUnidad::find(1)->only('id', 'codigo', 'descripcion');
                $clave_prod_serv = ClaveProdServ::find(5)->only('id', 'nombre');
                $this->factura_conceptos[] = [
                    'id' => null,
                    'cantidad' => 1,
                    'clave_unidad' => $clave_unidad,
                    'clave_unidad_id' => $clave_unidad['id'],
                    'clave_prod_serv' => $clave_prod_serv,
                    'clave_prod_serv_id' => $clave_prod_serv['id'],
                    'descripcion' => 'Nota de Crédito',
                    'precio_unitario' => $st
                ];
            } else {
                $this->factura_conceptos[0]['precio_unitario'] += $st;
            }
        }
    }

    public function addEmpresa($value)
    {
        $this->cliente_id = $value;
        $this->dispatchBrowserEvent('set-data-cliente_id', ['data' => [Cliente::find($this->cliente_id)->only('id', 'text')], 'term' => '', 'value' => $this->cliente_id]);
        //        $this->dispatchBrowserEvent('reApplySelect2');
    }

    public function showPdf()
    {
        $name = Factura::generatePdf($this->notaCredito->id, true);
        $this->iframeSrc = Request::root() . "/$name?" . time();
        $this->iframeContainerClass = 'show';
    }

    /**
     * @throws \Exception
     */
    public function timbrarFactura($id, $consecutivo = null)
    {
        if (!$consecutivo) {
            $this->emit('nuevo-consecutivo-factura', $id, 'facturacion.prefacturas.save-complemento');
            return;
        }
        $facturador = new Facturador(get_system_owner());
        $res = $facturador->timbrarFactura($id, $consecutivo);
        $this->emit('show-toast', $res['message'], $res['success'] ? 'success' : 'danger');
        if ($res['success'])
            $this->redirect(route('admin.pre-facturas.index'));
    }
}
