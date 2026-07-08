<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class Terminal
 *
 * @property integer $id_pos
 * @property string $nombre
 * @property string $identificador
 * @property string $comentarios
 * @property integer $sucursal_id
 * @property integer $suscripcion_id
 */
class Terminal extends Model
{
    use SoftDeletes;

    protected $table = 'tb_terminales';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_pos',
        'nombre',
        'identificador',
        'comentarios',
        'sucursal_id',
        'suscripcion_id'
    ];

    protected $appends = ['value', 'label'];

    public function rules()
    {
        return [
            'nombre' => ['required'],
            'identificador' => ['required'],
            'comentarios' => ['nullable'],
            'sucursal_id' => ['required', 'exists:tb_sucursales,id'],
            'suscripcion_id' => 'nullable|exists:tb_suscripciones,id'
        ];
    }

    public function messages()
    {
        return [
            'nombre.required' => 'Campo requerido.',
            'identificador.required' => 'Campo requerido.',
            'sucursal_id.required' => 'Campo requerido.',
            'sucursal_id.exists' => 'Sucursal no encontrada.',
            'suscripcion_id.exists' => 'Suscripción no encontrada.'
        ];
    }

    public function getValueAttribute()
    {
        return $this->getKey();
    }

    public function getLabelAttribute()
    {
        return $this->nombre ? "$this->nombre - $this->identificador" : $this->identificador;
    }

    public static function findByIdentificador($identificador)
    {
        $resource = Terminal::where('identificador', $identificador)->get();
        if ($resource->count() > 0)
            return $resource->first();
        return null;
    }

    public static function parseData($data = [])
    {
        foreach ($data as $key => $value) {
            if (in_array($key, ['sucursal_id']) && $value) {
                switch ($key) {
                    case 'sucursal_id':
                        $data['sucursal'] = DB::table('tb_sucursales')
                            ->selectRaw('id, nombre_comercial as nombre')->where('id', $value)->first()->nombre;
                        break;
                    case 'suscripcion_id':
                        $data['suscripcion'] = DB::table('tb_suscripciones as sub')
                            ->selectRaw('sub.id, CONCAT("Suscripción #", sub.id, " - ", IF(paquete.id IS NULL, "CUSTOM", paquete.nombre)) as nombre')
                            ->leftJoin('tb_paquetes as paquete', 'paquete.id', 'sub.paquete_id')
                            ->where('sub.id', $value)->first()->nombre;
                        break;
                }
            }
        }

        return $data;
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id')->withTrashed();
    }
    public function suscripcion()
    {
        return $this->belongsTo(Suscripcion::class, 'suscripcion_id');
    }
}
