<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class ItemTicketVK
 * @package App\Models\Administracion\CodificadoresFacturacion
 * @version January 12, 2021, 7:55 pm CST
 *
 * @property string $nombre
 * @property float $cantidad
 * @property string $asiento
 * @property integer $ticket_vk_id
 */
class ItemTicketVK extends Model
{
    use LogsActivity;

    public $table = 'tb_tickets_vk_items';

    public $fillable = [
        'nombre',
        'cantidad',
        'asiento',
        'ticket_vk_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'nombre' => 'string',
        'cantidad' => 'float',
        'asiento' => 'string',
        'ticket_vk_id' => 'integer'
    ];

    /**
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => 'El Item de Ticket VK ha sido creada.',
                    'updated' => 'El Item de Ticket VK actualizada.',
                    'deleted' => 'El Item de Ticket VK eliminada.',
                    'restored' => 'El Item de Ticket VK restaurada.',
                    'forceDeleted' => 'El Item de Ticket VK eliminada permanentemente.',
                    default => $eventName,
                };
            })
            ->useLogName('Item de Ticket VK')
            ->logExcept(['created_at', 'updated_at'])
            ->logOnlyDirty(); // Registra solo los campos que han cambiado
    }

    public function ticket_vk()
    {
        return $this->belongsTo(TicketVK::class, 'ticket_vk_id');
    }
    public function modificadores()
    {
        return $this->belongsToMany(ModificadorVK::class, 'tb_items_vk_modificadores', 'item_id', 'modificador_id');
    }
}
