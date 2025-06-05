<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class User
 *
 * @property string nombre
 * @property string descripcion
 */
class Role extends Model
{
    use LogsActivity;

    protected $table = 'tb_roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'nombre',
        'descripcion'
    ];

    protected $appends = ['value', 'label'];

    /**
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => "El Rol ha sido creado.",
                    'updated' => "El Rol ha sido actualizado.",
                    'deleted' => "El Rol ha sido eliminado.",
                    'restored' => "El Rol ha sido restaurado.",
                    'forceDeleted' => "El Rol ha sido eliminado permanentemente.",
                    default => $eventName,
                };
            })
            ->useLogName("Rol")
            ->logExcept(['created_at', 'updated_at'])
            ->logOnlyDirty(); // Registra solo los campos que han cambiado
    }

    public function rules()
    {
        return [
            'nombre' => ['required'],
            'descripcion' => 'nullable'
        ];
    }

    public function messages()
    {
        return [
            'nombre.required' => 'Campo requerido.'
        ];
    }

    public function getValueAttribute()
    {
        return $this->id;
    }

    public function getLabelAttribute()
    {
        return $this->name;
    }
}
