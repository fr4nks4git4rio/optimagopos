<?php

namespace App\Http\Livewire\Suscripciones;

use App\Http\Livewire\Layouts\Modal;
use App\Models\ClaveProdServ;
use App\Models\Factura;
use App\Models\Suscripcion;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class Activar extends Modal
{
    public Suscripcion $suscripcion;

    public $selectedSubscriptionData = [
        'id' => null,
        'cliente_nombre' => '',
        'plan_nombre' => '',
        'monto' => '',
        'frecuencia' => '',
        'proximo_pago' => '',
        'multiplicador' => ''
    ];

    public function mount()
    {
        $this->selectedSubscriptionData = [
            'id' => $this->suscripcion->id,
            'cliente_nombre' => Crypt::decrypt($this->suscripcion->cliente->nombre_comercial),
            'plan_nombre' => $this->suscripcion->paquete->nombre,
            'monto' => $this->suscripcion->precio_total,
            'frecuencia' => $this->suscripcion->periodicidad_pagos,
            'proximo_pago' => $this->suscripcion->fecha_inicio_pagos->format('d/m/Y'),
            'multiplicador' => $this->suscripcion->multiplicador_periodicidad
        ];
    }

    public function render()
    {
        return view('livewire.suscripciones.activar');
    }

    public function init()
    {
        if (user()->cannot('activar', $this->suscripcion)) {
            $this->emit('show-toast', 'No tiene permisos para realizar estar acción.', 'danger');
            $this->emit('closeModal');
            return;
        }
    }

    public function activar()
    {
        DB::beginTransaction();
        try {
            $this->suscripcion->estado = 'ACTIVA';
            $this->suscripcion->save();

            //TODO Logica para generar la primera factura

            $factura = new Factura();
            $factura->estado = 'PRECAPTURADA';
            $factura->fecha_pago = $this->suscripcion->fecha_inicio_pagos;
            $factura->fecha_emision = today();
            $factura->moneda = system_config('moneda_sistema');
            $factura->tipo_cambio = get_tipo_cambio_sistema(today()->format('Y-m-d'))->tasa;
            $factura->cliente_id = $this->suscripcion->cliente_id;
            $factura->serie_id = 1;
            $factura->forma_pago_id = 5;
            $factura->cfdi_id = null;
            $factura->metodo_pago_id = 2;
            $factura->tipo_comprobante_id = 1;
            $factura->porciento_iva = system_iva();
            $factura->propietario_id = get_system_owner()->id;
            $factura->comentarios = "Suscripción " . $this->suscripcion->paquete->nombre;
            $factura->suscripcion_id = $this->suscripcion->id;

            $subtotal = round($this->suscripcion->precio_total * $this->suscripcion->multiplicador_periodicidad, 2);

            $factura->subtotal = $subtotal;

            $iva = round($subtotal * (system_iva() / 100), 2);

            $factura->iva = $iva;
            $factura->total = $subtotal + $iva;
            $factura->cantidad_letras = convertir_numero_a_letras(round($factura->total, 2), $factura->moneda);
            $factura->del_sistema = 1;
            $factura->save();

            $factura->folio_interno = $factura->serie?->descripcion . $factura->id;
            $factura->save();

            $claveProdServ = ClaveProdServ::where('codigo', '81112501')->first();
            $factura->factura_conceptos()->create([
                'cantidad' => 1,
                'precio_unitario' => $subtotal,
                'descripcion' => "Suscripción " . $this->suscripcion->paquete->nombre,
                'clave_unidad_id' => $claveProdServ?->clave_unidad_id,
                'clave_prod_serv_id' => $claveProdServ?->id,
                'objeto_impuesto_id' => 2,
                'suscripcion_id' => $this->suscripcion->id
            ]);

            DB::commit();

            $this->emit('show-toast', 'Suscripción activada.');
            $this->emit('$refresh');
            $this->emit('closeModal');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->emit('show-toast', 'Error al activar la suscripción.', 'danger');
            return;
        }
    }
}
