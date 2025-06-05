<?php

namespace App\Http\Livewire\Facturas;

use App\Http\Livewire\Layouts\Modal;
use App\Models\Factura;
use App\Models\FacturaConcepto;
use Illuminate\Support\Facades\Storage;

class Delete extends Modal
{
    public $scope;
    public Factura $factura;
    public string $type;

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->type = 'Factura';
    }

    public function render()
    {
        return view('livewire.facturas.delete');
    }

    public function delete()
    {
        $tipo = 'Factura';
        if ($this->factura->factura_conceptos()->count() > 0)
            $this->factura->factura_conceptos->map(function (FacturaConcepto $fc) {
                $fc->delete();
            });

        activity("$tipo Eliminada")
            ->by(user())
            ->withProperties($this->factura->toArray())
            ->log("Se ha eliminado definitivamente la $tipo con folio: {$this->factura->folio_interno}");

        $this->factura->delete();

        $this->emit('show-toast', "$this->type eliminada satisfactoriamente.");
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
