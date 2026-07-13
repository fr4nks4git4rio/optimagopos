<?php

namespace App\Http\Livewire\FacturasSistema;

use App\Http\Livewire\Layouts\Modal;
use App\Models\Factura;
use App\Models\FacturaConcepto;
use App\Models\Ticket;
use App\Models\TicketOperacion;
use Illuminate\Support\Facades\Storage;

class Delete extends Modal
{
    public $scope;
    public Factura $factura;
    public string $type;

    protected $listeners = ['$refresh'];

    public function mount()
    {
        if ($this->factura->es_complemento)
            $this->type = __('site.common.complement');
        else if ($this->factura->es_nota_credito)
            $this->type = __('site.common.credit_note');
        else
            $this->type = __('site.common.invoice');
    }

    public function render()
    {
        return view('livewire.facturas-sistema.delete');
    }

    public function init()
    {
        if (user()->cannot('deleteFacturaSistema', $this->factura)) {
            $this->emit('show-toast', __('site.common.client_no_permissions'), 'danger');
            $this->emit('closeModal');
            return;
        }
    }

    public function delete()
    {
        if ($this->factura->factura_conceptos()->count() > 0)
            $this->factura->factura_conceptos->map(function (FacturaConcepto $fc) {
                $fc->delete();
            });
        if ($this->factura->tickets()->count() > 0)
            $this->factura->tickets->map(function (Ticket $ticket) {
                $ticket->factura_id = null;
                $ticket->save();
            });
        if ($this->factura->ticket_operaciones()->count() > 0)
            $this->factura->tickets->map(function (TicketOperacion $ticketOperacion) {
                $ticketOperacion->factura_id = null;
                $ticketOperacion->save();
            });
        if ($this->factura->complementos()->count() > 0)
            $this->factura->complementos()->detach();
        if ($this->factura->facturas()->count() > 0)
            $this->factura->facturas()->detach();
        if ($this->factura->facturas_relacionadas()->count() > 0)
            $this->factura->facturas_relacionadas()->detach();
        if ($this->factura->ingresos()->count() > 0)
            $this->factura->ingresos()->detach();
        if ($this->factura->nota_credito_ingresos()->count() > 0)
            $this->factura->nota_credito_ingresos()->detach();

        if ($this->factura->es_complemento)
            $log = __('site.invoices.delete.complement_deleted');
        elseif ($this->factura->es_nota_credito)
            $log = __('site.invoices.delete.credit_note_deleted');
        else
            $log = __('site.invoices.delete.invoice_deleted');

        activity($log)
            ->by(user())
            ->withProperties($this->factura->toArray())
            ->log(__('site.invoices.delete.log_details', ['type' => $this->type, 'folio' => $this->factura->folio_interno]));

        $this->factura->delete();

        $this->emit('show-toast', __('site.invoices.delete.deleted_successfully', ['type' => $this->type]));
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
