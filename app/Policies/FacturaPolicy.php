<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FacturaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Manager']) && $user->can('invoices-viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Factura $factura): bool
    {
        if ($factura->del_sistema)
            return false;

        if (
            $user->hasAnyRole(['Admin', 'Manager'])
            && $user->can('invoices-view')
            && $user->cliente_id == $factura->propietario_id
            && in_array($factura->propietario->suscripcion_id, $user->suscripciones_activas()->pluck('id')->toArray())
        )
            return true;

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Manager']) && $user->can('invoices-createInvoice');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Factura $factura): bool
    {
        if ($factura->del_sistema)
            return false;

        if (
            $user->hasAnyRole(['Admin', 'Manager'])
            && $user->can('invoices-updateInvoice')
            && $user->cliente_id ==  $factura->propietario_id
            && in_array($factura->propietario->suscripcion_id, $user->suscripciones_activas()->pluck('id')->toArray())
        )
            return true;

        return false;
    }

    public function stamp(User $user, Factura $factura): bool
    {
        if ($factura->del_sistema)
            return false;

        if (
            $user->hasAnyRole(['Admin', 'Manager'])
            && $user->can('invoices-stamp')
            && $user->cliente_id ==  $factura->propietario_id
            && in_array($factura->propietario->suscripcion_id, $user->suscripciones_activas()->pluck('id')->toArray())
        )
            return true;

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Factura $factura): bool
    {
        if ($factura->del_sistema)
            return false;

        if (
            $user->hasAnyRole(['Admin', 'Manager'])
            && $user->can('invoices-delete')
            && $user->cliente_id ==  $factura->propietario_id
            && in_array($factura->propietario->suscripcion_id, $user->suscripciones_activas()->pluck('id')->toArray())
        )
            return true;

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Factura $factura): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Factura $factura): bool
    {
        return false;
    }

    public function cancel(User $user, Factura $factura): bool
    {
        if ($factura->del_sistema)
            return false;

        if (
            $user->hasAnyRole(['Admin', 'Manager'])
            && $user->can('invoices-cancel')
            && $user->cliente_id ==  $factura->propietario_id
            && in_array($factura->propietario->suscripcion_id, $user->suscripciones_activas()->pluck('id')->toArray())
        )
            return true;

        return false;
    }

    //TODO Facturas del sistema
    /**
     * Determine whether the user can view any models.
     */
    public function viewAnyFacturaSistema(User $user): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager']))
            return false;

        return $user->can('invoices-viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function viewFacturaSistema(User $user, Factura $factura): bool
    {
        if (!$factura->del_sistema)
            return false;

        if ($user->hasAnyRole(['Admin', 'Manager']))
            return false;

        return $user->can('invoices-view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function createFacturaSistema(User $user): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager']))
            return false;

        return $user->can('invoices-createInvoice');
    }
    public function createComplementoSistema(User $user): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager']))
            return false;

        return $user->can('invoices-createComplement');
    }
    public function createNotaCreditoSistema(User $user): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager']))
            return false;

        return $user->can('invoices-createCreditNote');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function updateFacturaSistema(User $user, Factura $factura): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager']) || !$factura->del_sistema)
            return false;

        if ($factura->es_complemento)
            return $user->can('invoices-updateComplement');
        if ($factura->es_nota_credito)
            return $user->can('invoices-updateCreditNote');
        return $user->can('invoices-updateInvoice');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function stampFacturaSistema(User $user, Factura $factura): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager']) || !$factura->del_sistema)
            return false;

        return $user->can('invoices-stamp');
    }

    public function deleteFacturaSistema(User $user, Factura $factura): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager']) || !$factura->del_sistema)
            return false;

        return $user->can('invoices-delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restoreFacturaSistema(User $user, Factura $factura): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDeleteFacturaSistema(User $user, Factura $factura): bool
    {
        return false;
    }

    public function cancelFacturaSistema(User $user, Factura $factura): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager']) || !$factura->del_sistema)
            return false;

        return $user->can('invoices-cancel');
    }
}
