<?php

namespace App\Http\Livewire\FacturasSistema;

use App\Models\Factura;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ConsecutivoFactura extends Component
{
    public $scope;
    public $show = '';
    public $title = 'Confirmar Consecutivo';
    public $factura;
    public $consecutivo;
    public $modal_id = 'consecutivo-factura';

    protected $listeners = ['$refresh', 'nuevo-consecutivo-factura' => 'nuevoConsecutivo'];

    public function render()
    {
        return view('livewire.facturas-sistema.consecutivo-factura');
    }

    public function nuevoConsecutivo($id_factura, $scope = null)
    {
        $this->factura = Factura::find($id_factura);
        $this->consecutivo = Factura::internalSheetGenerator($this->factura->serie_id, modo_facturacion_sistema());
        $this->scope = $scope;
        $this->show = 'show';
    }

    public function guardar()
    {
        $this->validate(
            [
                'consecutivo' => ['required', Rule::unique('tb_facturas', 'folio_interno')->ignore($this->factura->id)],
            ],
            // [
            //     'consecutivo.required' => 'Campo requerido.',
            //     'consecutivo.unique' => 'El consecutivo ya existe.',
            // ]
        );

        $this->factura->folio_interno = $this->consecutivo;
        $this->factura->save();
        $this->emit('$refresh');

        $this->emitTo($this->scope, 'consecutivo-factura-generado', $this->factura->id, $this->consecutivo);
        $this->show = '';
    }
}
