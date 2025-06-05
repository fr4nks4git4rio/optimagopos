<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class TicketProductoCorreccion
 * @package App\Models
 * @version January 12, 2021, 7:55 pm CST
 *
 * @property string nombre
 * @property float precio
 * @property integer cantidad
 * @property integer producto_id
 */
class TicketProductoCorreccion extends Model
{
    public $table = 'tb_ticket_producto_correcciones';

    public $fillable = [
        'nombre',
        'precio',
        'cantidad',
        'producto_id',
    ];

    public function producto()
    {
        return $this->belongsTo(TicketProducto::class, 'producto_id');
    }
}
