<?php

namespace App\Http\Livewire\FacturasSistema;

use App\Models\Cfdi;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\FormaPago;
use App\Models\Serie;
use App\Rules\DataClientRule;
use App\Services\Timbrado\Facturador;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class SaveComplemento extends Component
{
    public $scope;
    public Factura $complemento;
    public $folio_interno;
    public $lugar_expedicion;
    public $fecha_emision;
    public $fecha_pago;
    public $fecha_emision_str;
    public $numero_operacion;
    public $comentarios;
    public $cantidad_letras;
    public $estado;
    public $moneda;
    public $tipo_cambio;
    public $cuenta_origen_id;
    public $cuenta_destino_id;
    public $cfdi_id;
    public $forma_pago_id;
    public $cliente_id;
    public $serie_id;
    public $facturas = [];

    public $banco_origen_nombre = '';
    public $banco_origen_rfc = '';
    public $banco_destino_nombre = '';
    public $banco_destino_rfc = '';

    public $facturasAll = [];
    public $cuentasOrigen = [];
    public $cuentasDestino = [];

    public $iframeContainerClass = '';
    public $iframeSrc = '';


    protected $listeners = ['$refresh', 'consecutivo-factura-generado' => 'timbrarComplemento'];

    public function mount($id = null)
    {
        if ($id) {
            $this->complemento = Factura::find($id);
        } else {
            $this->complemento = new Factura();
        }

        $this->folio_interno = $this->complemento->exists ? $this->complemento->folio_interno : '';
        $this->fecha_pago = $this->complemento->exists ? $this->complemento->fecha_pago_en : now()->format('Y-m-d');
        $this->fecha_emision = $this->complemento->exists ? $this->complemento->fecha_emision_en : now()->format('Y-m-d H:i:s');
        $this->fecha_emision_str = $this->complemento->exists ? $this->complemento->fecha_emision_str : now()->format('d/m/Y H:i');
        $this->lugar_expedicion = $this->complemento->exists ? $this->complemento->lugar_expedicion : '';
        $this->numero_operacion = $this->complemento->exists ? $this->complemento->numero_operacion : '';
        $this->comentarios = $this->complemento->exists ? $this->complemento->comentarios : '';
        $this->cantidad_letras = $this->complemento->exists ? $this->complemento->cantidad_letras : '';
        $this->estado = $this->complemento->exists ? $this->complemento->estado : 'CAPTURADA';
        $this->moneda = $this->complemento->exists ? $this->complemento->moneda : 'MXN';
        $this->tipo_cambio = $this->complemento->exists && $this->complemento->tipo_cambio ? $this->complemento->tipo_cambio : get_tipo_cambio_sistema()->tasa;
        if ($this->complemento->exists) {
            $this->cfdi_id = $this->complemento->cfdi_id;
            $this->forma_pago_id = $this->complemento->forma_pago_id;
            $this->cliente_id = $this->complemento->cliente_id;
            $this->serie_id = $this->complemento->serie_id;
            $this->cuenta_origen_id = $this->complemento->cuenta_origen_id;
            $this->cuenta_destino_id = $this->complemento->cuenta_destino_id;
            $this->facturas = $this->complemento->facturas()->get()->map(function (Factura $factura) {
                $factura->fecha = $factura->fecha_certificacion ? $factura->fecha_certificacion->format('d/m/Y') : '';
                $factura->can_be_removed = $factura->complementos()->where('estado', '!=', 'CANCELADA')->count() == 1;
                $factura->seleccionada = true;
                $factura->no_parcialidad = $factura->pivot->no_parcialidad;
                $factura->balance_previo = $factura->pivot->balance_previo;
                $factura->balance_previo_temp = $factura->pivot->balance_previo;
                $factura->importe_pagado = $factura->pivot->importe_pagado;
                return $factura->only('id', 'folio_interno', 'fecha', 'moneda', 'tipo_cambio', 'total', 'seleccionada', 'can_be_removed', 'no_parcialidad', 'balance_previo', 'balance_previo_temp', 'importe_pagado', 'metodo_pago_id');
            })->toArray();
        }

        // $this->cuentasDestino = CuentaBancaria::where('empresa_id', get_owner_company()->id)->get()->map->only(['value', 'label'])->toArray();
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
        $this->facturasAll = [];
        $this->facturas = [];
        $this->cuentasOrigen = [];
        if ($value) {
            $this->facturasAll = $this->loadFacturasTimbradasCliente($value, $this->complemento);
            // $this->cuentasOrigen = CuentaBancaria::where('empresa_id', $value)->get()->only(['value', 'label'])->toArray();
        }
    }

    public function updatedCuentaOrigenId($value)
    {
        $this->banco_origen_nombre = '';
        $this->banco_origen_rfc = '';
        // if ($value) {
        //     $cuenta = CuentaBancaria::find($value);
        //     $this->banco_origen_nombre = $cuenta->banco->nombre;
        //     $this->banco_origen_rfc = $cuenta->banco->rfc;
        // }
    }

    public function updatedCuentaDestinoId($value)
    {
        $this->banco_destino_nombre = '';
        $this->banco_destino_rfc = '';
        // if ($value) {
        //     $cuenta = CuentaBancaria::find($value);
        //     $this->banco_destino_nombre = $cuenta->banco->nombre;
        //     $this->banco_destino_rfc = $cuenta->banco->rfc;
        // }
    }

    public function updatedMoneda($value)
    {
        foreach ($this->facturas as $index => $factura) {
            $this->comprobarSaldoFactura($index);
        }
    }

    public function loadInitialData()
    {
        if ($this->complemento->exists) {
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
                $this->facturasAll = $this->loadFacturasTimbradasCliente($this->cliente_id, $this->complemento);
            }
        } else {
            // $this->serie_id = 4;

            // $this->dispatchBrowserEvent('set-data-serie_id', ['data' => [Serie::find(4)->only('id', 'text')], 'term' => '', 'value' => 4]);
        }
    }

    public function loadFacturasTimbradasCliente($client_id, $complement = null)
    {
        $facturas = collect();
        $ids = [];
        if ($complement) {
            $complement->facturas->map(static function ($invoice) use (&$facturas, &$ids) {
                $invoice->fecha = $invoice->fecha_certificacion ? $invoice->fecha_certificacion->format('d/m/Y') : '';
                $invoice->seleccionada = true;
                $invoice->balance_previo = round($invoice->pivot->balance_previo, 2);
                $invoice->balance_previo_temp = $invoice->balance_previo;
                $invoice->no_parcialidad = $invoice->pivot->no_parcialidad;
                $invoice->importe_pagado = $invoice->pivot->importe_pagado;
                $invoice->can_be_removed = false;
                $ids[] = $invoice->id;
                $facturas->push($invoice);
            });
        }

        $facturas_tmp = Factura::where('cliente_id', $client_id)
            ->where('propietario_id', get_system_owner()->id)
            ->where('es_complemento', 0)
            ->where(function ($query) {
                $query->where('estado', 'COBRADA')
                    ->orWhere(function ($query) {
                        $query->where('estado', 'TIMBRADA')
                            ->whereHas('ingresos');
                    });
            })
            ->whereNotIn('id', $ids)
            ->get();

        $facturas_tmp->map(function (Factura $invoice) use (&$facturas, $complement) {
            $paid = 0;
            $complementos = DB::table('tb_facturas_complementos as f_c')
                ->select('f_c.importe_pagado')
                ->join('tb_facturas as c', 'f_c.complemento_id', '=', 'c.id')
                ->where('f_c.factura_id', $invoice->id)
                ->whereNotIn('c.estado', ['CANCELADA', 'PROCESO CANCELACION'])
                ->get();

            $complementos->map(function ($comp) use (&$paid) {
                $paid += (float)$comp->importe_pagado;
            });

            $ingresos_nota_credito = DB::table('tb_ingresos_facturas as ingreso')
                ->leftJoin('tb_facturas as nota_credito', 'nota_credito.id', 'ingreso.nota_credito_id')
                ->where('ingreso.factura_id', $invoice->id)
                ->sum('nota_credito.total');
            $paid += max($ingresos_nota_credito, 0);

            $paid = round($paid, 2);
            $total = round((float)$invoice->total, 2);
            $invoice->fecha = $invoice->fecha_certificacion ? $invoice->fecha_certificacion->format('d/m/Y') : '';
            //            dd("Total: $total", "Pagado: $paid");
            if ((abs($total - $paid) > 1)) {
                $invoice = Factura::loadPreviousAndPaidInvoiceInformation($invoice);
                $invoice->seleccionada = false;
                $facturas->push($invoice);
            }
        });

        return $facturas->toArray();
    }

    public function render()
    {
        return view('livewire.facturas-sistema.save-complemento');
    }

    public function getTotalProperty()
    {
        return array_reduce($this->facturas, function ($carry, $item) {
            $carry += max($item['importe_pagado'], 0);
            return $carry;
        });
    }

    public function getSubtotalProperty()
    {
        return round($this->total / (1 + system_iva()), 2);
    }

    public function getIvaProperty()
    {
        return $this->total - $this->subtotal;
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

    public function getMostrarDatosCuentasProperty()
    {
        return $this->forma_pago_id > 1;
    }

    public function saldoFactura($index)
    {
        if ($this->moneda === 'MXN') {
            if ($this->facturas[$index]['moneda'] === 'USD') {
                $change = !is_numeric($this->tipo_cambio) ? 0 : $this->tipo_cambio;
                $balance_previo = round($this->facturas[$index]['balance_previo_temp'] * $change, 2);
            } else {
                $balance_previo = $this->facturas[$index]['balance_previo_temp'];
            }
        } else {
            if ($this->facturas[$index]['moneda'] === 'MXN') {
                $change = !is_numeric($this->tipo_cambio) ? 0 : $this->tipo_cambio;
                $balance_previo = round($this->facturas[$index]['balance_previo_temp'] / $change, 2);
            } else {
                $balance_previo = $this->facturas[$index]['balance_previo_temp'];
            }
        }
        if (!is_numeric($this->facturas[$index]['importe_pagado']))
            $this->facturas[$index]['importe_pagado'] = 0;
        return $balance_previo - max($this->facturas[$index]['importe_pagado'], 0);
    }

    public function comprobarSaldoFactura($index)
    {
        if ($this->saldoFactura($index) < 0) {
            $this->facturas[$index]['importe_pagado'] = 0;
            $this->emit('show-toast', 'El Saldo no puede ser menor que 0.', 'danger');
        } else if ($this->facturas[$index]['metodo_pago_id'] == 1) {
            $import_to_pay = 0;
            if ($this->moneda === 'MXN') {
                if ($this->facturas[$index]['moneda'] === 'MXN')
                    $import_to_pay = $this->facturas[$index]['balance_previo_temp'];
                else
                    $import_to_pay = round($this->facturas[$index]['balance_previo_temp'] * $this->tipo_cambio, 2);
            } else {
                if ($this->facturas[$index]['moneda'] === 'USD')
                    $import_to_pay = $this->facturas[$index]['balance_previo_temp'];
                else
                    $import_to_pay = round($this->facturas[$index]['balance_previo_temp'] / $this->tipo_cambio, 2);
            }
            $this->facturas[$index]['importe_pagado'] = $import_to_pay;
            $this->emit('show-toast', 'La factura no permite pago en varias parcialidades. Es necesario pagarla en su totalidad.', 'warning');
        }
        $this->emit('$refresh');
    }

    public function saveComplemento($timbrando = false)
    {
        $rules = [
            'estado' => ['nullable'],
            'comentarios' => ['nullable'],
            'fecha_emision' => ['required'],
            'fecha_pago' => ['required'],
            'cliente_id' => ['required', 'exists:tb_clientes,id', new DataClientRule()],
            'cfdi_id' => ['required', 'exists:tb_cfdis,id'],
            'serie_id' => ['required', 'exists:tb_series,id'],
            'forma_pago_id' => ['required_if:es_nomina,false', 'exists:tb_forma_pagos,id'],
            'cuenta_destino_id' => ['nullable'],
            'cuenta_origen_id' => ['nullable'],
            'moneda' => ['required'],
            'tipo_cambio' => ['required'],
            'numero_operacion' => [Rule::requiredIf($this->forma_pago_id != 1)],
            'facturas' => ['array', 'min:1'],
            'facturas.*.importe_pagado' => ['required', 'numeric', 'gt:0'],
            'facturas.*.id' => ['required'],
            'facturas.*.no_parcialidad' => ['nullable'],
            'facturas.*.balance_previo' => ['nullable'],
        ];
        $messages = [
            'fecha_pago.required' => 'Campo requerido.',
            'cliente_id.required' => 'Campo requerido.',
            'cfdi_id.required' => 'Campo requerido.',
            'serie_id.required' => 'Campo requerido.',
            'forma_pago_id.required_if' => 'Campo requerido.',
            'moneda.required' => 'Campo requerido.',
            'tipo_cambio.required' => 'Campo requerido.',
            'numero_operacion.required' => 'Campo requerido.',
            'facturas.min' => 'Debe seleccionar al menos una factura.',
            'facturas.*.importe_pagado.required' => 'Campo requerido.',
            'facturas.*.importe_pagado.numeric' => 'Valor no válido.',
            'facturas.*.importe_pagado.gt' => 'El importe debe ser mayor a 0.'
        ];
        $data = $this->validate(
            $rules,
            // $messages
        );
        if (!$this->complemento->exists) {
            $data['porciento_iva'] = system_iva();
            $data['propietario_id'] = get_system_owner()->id;
            $data['es_complemento'] = 1;
            $data['estado'] = 'CAPTURADA';
            $data['lugar_expedicion'] = optional(get_system_owner()->direccion_fiscal)->codigo_postal;
            $data['tipo_comprobante_id'] = 5;
        }

        $data['total'] = $this->total;
        $data['iva'] = $this->iva;
        $data['subtotal'] = $this->subtotal;
        $data['cantidad_letras'] = convertir_numero_a_letras($this->total, $this->moneda);
        $data['del_sistema'] = 1;
        $data['es_complemento'] = 1;

        $this->complemento->fill(Arr::except($data, ['facturas']))->save();

        if ($this->complemento->wasRecentlyCreated) {
            $this->complemento->folio_interno = $this->complemento->serie->descripcion . $this->complemento->id;
            $this->complemento->save();
        }

        $this->complemento->facturas()->detach();
        foreach ($data['facturas'] as $factura) {
            $this->complemento->facturas()->attach($factura['id'], [
                'no_parcialidad' => $factura['no_parcialidad'],
                'balance_previo' => (float)$factura['balance_previo'],
                'importe_pagado' => (float)$factura['importe_pagado']
            ]);
        }

        activity('Complementos')
            ->performedOn($this->complemento)
            ->causedBy(auth()->user())
            ->withProperties(['complemento_id' => $this->complemento->id])
            ->log(($this->complemento->wasRecentlyCreated ? 'Creado' : 'Modificado') . " Complemento con id: " . $this->complemento->id);

        $this->emit('show-toast', 'Complemento guardado.');
        if (!$timbrando)
            $this->redirect(route('admin.complementos.save', $this->complemento->id));
    }

    public function checkFactura($index, $id)
    {
        if ($this->facturasAll[$index]['seleccionada']) {
            $this->eliminarFactura($id);
        } else {
            $this->moneda = $this->facturasAll[$index]['moneda'];
            $this->facturas[] = $this->facturasAll[$index];
        }
        //        $this->cantidad_letras = convertir_numero_a_letras($this->total, $this->moneda);
    }

    public function eliminarFactura($id)
    {
        $factura = array_values(array_filter($this->facturas, function ($element) use ($id) {
            return $element['id'] == $id;
        }))[0];
        if (!$this->complemento->exists || $factura['can_be_removed']) {
            $this->facturas = array_values(array_filter($this->facturas, function ($element) use ($id) {
                return $element['id'] != $id;
            }));

            Arr::map($this->facturasAll, function ($element, $index) use ($id) {
                if ($element['id'] == $id) {
                    $this->facturasAll[$index]['seleccionada'] = false;
                }
            });
            $this->emit('unselect-factura', $id);
        } else {
            $this->emit('show-toast', 'La factura no puede ser eliminada. Ha sido utilizada en otro(s) complemento(s) posterior al actual.', 'danger');
        }
    }

    public function eliminarConceptoFactura($index)
    {
        if ($this->factura_conceptos[$index]['cotizacion'] != null) {
            foreach ($this->cotizaciones as $i => $element) {
                if ($element['id'] == $this->factura_conceptos[$index]['cotizacion']['id']) {
                    $this->cotizaciones[$i]['seleccionada'] = false;
                    $this->comentarios = Str::replace("Cotización " . $element['consecutivo'] . ", ", '', $this->comentarios);
                    $this->comentarios = Str::replace("Cotización " . $element['consecutivo'], '', $this->comentarios);
                    $this->emit('unselect-cotizacion', $element['id']);
                }
            }
        }
        array_splice($this->factura_conceptos, $index, 1);
    }
    public function showPdf()
    {
        $name = Factura::generateComplementoPdf($this->complemento->id, true);
        $this->iframeSrc = Request::root() . "/$name?" . time();
        $this->iframeContainerClass = 'show';
    }

    /**
     * @throws \Exception
     */
    public function timbrarComplemento($id, $consecutivo = null)
    {
        if (!$consecutivo) {
            $this->emit('nuevo-consecutivo-factura', $id, 'facturacion.prefacturas.save-complemento');
            return;
        }
        $this->saveComplemento(true);
        $facturador = new Facturador(get_system_owner());
        $res = $facturador->timbrarComplemento($id, $consecutivo);
        $this->emit('show-toast', $res['message'], $res['success'] ? 'success' : 'danger');
        if ($res['success'])
            $this->redirect(route('prefacturas.index'));
    }
}
