<?php

namespace App\Livewire\FacturasSistema;

use App\Models\Factura;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ConsecutivoFactura extends Component
{
    public $scope;
    public $show = '';
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
        $this->dispatch('show-sub-modal', $this->modal_id);
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
        $this->dispatch('$refresh');

        $this->dispatch('consecutivo-factura-generado', $this->factura->id, $this->consecutivo)->to($this->scope);
        $this->dispatch('hide-sub-modal', $this->modal_id);
    }
}
