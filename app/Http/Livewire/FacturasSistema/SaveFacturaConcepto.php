<?php

namespace App\Http\Livewire\FacturasSistema;

use App\Models\ClaveProdServ;
use App\Models\ClaveUnidad;
use App\Models\ObjetoImpuesto;
use Livewire\Component;

class SaveFacturaConcepto extends Component
{
    public $scope;
    public $show = '';
    public $title = '';
    public $index;
    public $id_concepto = null;
    public $cantidad = 1;
    public $descripcion = null;
    public $precio_unitario = null;
    public $clave_unidad_id = null;
    public $clave_prod_serv_id = null;
    public $objeto_impuesto_id = null;
    public $modal_id = 'save-factura-concepto';

    protected $listeners = ['$refresh', 'nuevo-concepto-factura' => 'nuevoConcepto', 'editar-concepto-factura' => 'editarConcepto'];

    public function mount() {}

    public function render()
    {
        return view('livewire.facturas-sistema.save-factura-concepto', [
            'claveUnidades' => ClaveUnidad::all()->map->only(['value', 'label', 'id', 'codigo'])->toArray(),
            'objetosImpuestos' => ObjetoImpuesto::all()->map->only(['value', 'label', 'id', 'clave'])->toArray()
        ]);
    }

    public function hydrate()
    {
        $this->dispatchBrowserEvent('reApplySelect2');
    }

    public function updated()
    {
        $this->dispatchBrowserEvent('reApplySelect2');
    }

    public function updatedClaveProdServId($value)
    {
        $this->clave_unidad_id = null;
        if ($value) {
            $this->clave_unidad_id = ClaveProdServ::find($value)->clave_unidad_id;
        }
    }

    public function nuevoConcepto($scope = null)
    {
        $this->scope = $scope;
        $this->title = 'Nuevo Concepto';
        $this->index = null;
        $this->id_concepto = null;
        $this->cantidad = 1;
        $this->descripcion = null;
        $this->precio_unitario = null;
        $this->clave_unidad_id = null;
        $this->clave_prod_serv_id = null;
        $this->objeto_impuesto_id = null;
        $this->show = 'show';

        $this->dispatchBrowserEvent('set-data-clave_prod_serv_id', ['data' => [], 'term' => '', 'value' => null]);
    }

    public function editarConcepto($index, $concepto, $scope = null)
    {
        $this->scope = $scope;
        $this->title = 'Editar Concepto';
        $this->index = $index;
        $this->id_concepto = $concepto['id'];
        $this->cantidad = $concepto['cantidad'];
        $this->descripcion = $concepto['descripcion'];
        $this->precio_unitario = round($concepto['precio_unitario'], 2);
        $this->clave_unidad_id = $concepto['clave_unidad_id'];
        $this->clave_prod_serv_id = $concepto['clave_prod_serv_id'];
        $this->objeto_impuesto_id = $concepto['objeto_impuesto_id'];
        $this->show = 'show';

        if ($this->clave_prod_serv_id) {
            $this->dispatchBrowserEvent('set-data-clave_prod_serv_id', ['data' => [(array)ClaveProdServ::find($this->clave_prod_serv_id)->only(['id', 'text'])], 'term' => '', 'value' => $this->clave_prod_serv_id]);
        } else {
            $this->dispatchBrowserEvent('set-data-clave_prod_serv_id', ['data' => [], 'term' => '', 'value' => null]);
        }
    }

    public function guardar()
    {
        $data = $this->validate([
            'cantidad' => ['required', 'numeric', 'min:1'],
            'precio_unitario' => ['required', 'numeric', 'min:0.01'],
            'clave_unidad_id' => ['required', 'exists:tb_clave_unidades,id'],
            'clave_prod_serv_id' => ['required', 'exists:tb_clave_prod_servs,id'],
            'objeto_impuesto_id' => ['required', 'exists:tb_objetos_impuesto,id'],
            'descripcion' => ['required'],
        ], [
            'cantidad.min' => 'La cantidad debe ser mayor o igual a 1.',
            'cantidad.required' => 'Campo requerido.',
            'precio_unitario.required' => 'Campo requerido.',
            'precio_unitario.min' => 'El precio debe ser mayor o igual a 1.',
            'clave_unidad_id.required' => 'Campo requerido.',
            'clave_unidad_id.exists' => 'Clave no encontrada.',
            'clave_prod_serv_id.required' => 'Campo requerido.',
            'clave_prod_serv_id.exists' => 'Clave no encontrada.',
            'objeto_impuesto_id.required' => 'Campo requerido.',
            'objeto_impuesto_id.exists' => 'Objeto no encontrado.',
            'descripcion.required' => 'Campo requerido.',
        ]);

        if ($this->index === null)
            $this->emitTo($this->scope, 'concepto-creado', $data);
        else
            $this->emitTo($this->scope, 'concepto-modificado', $this->index, $data);

        $this->show = '';
    }
}
