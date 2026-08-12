<?php

namespace App\Livewire\Facturas;

use App\Livewire\Layouts\Modal;
use App\Models\Factura;
use App\Models\FacturaConcepto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class Delete extends Modal
{
    public $scope;
    public Factura $factura;
    public string $type;

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->type = __('site.common.invoice');
    }

    public function render()
    {
        return view('livewire.facturas.delete');
    }

    public function init()
    {
        if (user()->cannot('delete', $this->factura)) {
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            $this->dispatch('closeModal');
            return;
        }
    }

    public function delete()
    {
        DB::beginTransaction();

        $folio = $this->factura->folio_interno;
        try {
            if ($this->factura->factura_conceptos()->count() > 0)
                $this->factura->factura_conceptos->map(function (FacturaConcepto $fc) {
                    $fc->delete();
                });
            $this->factura->delete();
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ha ocurrido un error cancelando la factura. Error: {$e->getMessage()}");
            $this->dispatch('show-toast', __('site.invoices.delete.delete_error'), 'danger');
            return;
        }
        activity(__('site.invoices.delete.log_invoice_deleted'))
            ->by(user())
            ->event('deleted')
            ->withProperties($this->factura->getAttributes())
            ->log(__('site.invoices.delete.log_invoice_deleted_detail', ['folio' => $folio]));

        $this->dispatch('show-toast', __('site.invoices.delete.invoice_deleted'), 'success');
        $this->dispatch('$refresh');
        $this->dispatch('closeModal');
    }
}
