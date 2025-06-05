<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class TicketImpuesto
 * @package App\Models
 * @version January 12, 2021, 7:55 pm CST
 *
 * @property string nombre
 * @property float monto
 * @property integer ticket_id
 */
class TicketImpuesto extends Model
{
    public $table = 'tb_ticket_impuestos';

    public $fillable = [
        'nombre',
        'monto',
        'ticket_id'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }
}
