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
 * @property string identificador
 * @property string comentarios
 * @property integer sucursal_id
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
        'identificador',
        'comentarios',
        'sucursal_id'
    ];

    public function rules()
    {
        return [
            'identificador' => ['required'],
            'comentarios' => ['nullable'],
            'sucursal_id' => ['required', 'exists:tb_sucursales,id']
        ];
    }

    public function messages()
    {
        return [
            'identificador.required' => 'Campo requerido.',
            'sucursal_id.required' => 'Campo requerido.',
            'sucursal_id.exists' => 'Sucursal no encontrada.'
        ];
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
                }
            }
        }

        return $data;
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
}
