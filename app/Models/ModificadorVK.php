<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class ModificadorVK
 * @package App\Models\Administracion\CodificadoresFacturacion
 * @version January 12, 2021, 7:55 pm CST
 *
 * @property string $nombre
 */
class ModificadorVK extends Model
{
    use LogsActivity;

    public $table = 'tb_modificadores_vk';

    public $fillable = [
        'nombre'
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
                    'created' => 'EL Modificador VK ha sido creada.',
                    'updated' => 'EL Modificador VK ha sido actualizada.',
                    'deleted' => 'EL Modificador VK ha sido eliminada.',
                    'restored' => 'EL Modificador VK ha sido restaurada.',
                    'forceDeleted' => 'EL Modificador VK ha sido eliminada permanentemente.',
                    default => $eventName,
                };
            })
            ->useLogName('Modificador VK')
            ->logExcept(['created_at', 'updated_at'])
            ->logOnlyDirty(); // Registra solo los campos que han cambiado
    }

    public function items_vk()
    {
        return $this->belongsToMany(ItemTicketVK::class, 'tb_items_vk_modificadores', 'modificador_id', 'item_id');
    }
}
