<?php

namespace App\Models;

use App\Models\RegimenFiscal;
use App\Models\Direccion;
use App\Rules\RfcRule;
use App\Rules\RuleUnique;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Class Sucursal
 * @package App\Models
 * @version October 6, 2019, 12:01 pm CDT
 *
 * @property string nombre_comercial
 * @property string razon_social
 * @property string rfc
 * @property string correo
 * @property string telefono
 * @property string logo
 * @property integer cliente_id
 * @property integer direccion_fiscal_id
 * @property integer regimen_fiscal_id
 */
class Sucursal extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    public $table = 'tb_sucursales';

    public $fillable = [
        'nombre_comercial',
        'razon_social',
        'rfc',
        'correo',
        'telefono',
        'logo',
        'cliente_id',
        'direccion_fiscal_id',
        'regimen_fiscal_id'
    ];

    protected $appends = ['value', 'label', 'direccion_text', 'direccion_plain', 'logo_uri', 'codigo_postal'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'nombre_comercial' => 'string',
        'razon_social' => 'string',
        'rfc' => 'string',
        'correo' => 'string',
        'telefono' => 'string',
        'cliente_id' => 'integer',
        'direccion_fiscal_id' => 'integer',
        'regimen_fiscal_id' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public function rules()
    {

    }

    public function messages()
    {

    }

    public static function encryptInfo($sucursal)
    {
        if (is_array($sucursal)) {
            $sucursal['nombre_comercial'] = isset($sucursal['nombre_comercial']) && $sucursal['nombre_comercial'] ? Crypt::encrypt($sucursal['nombre_comercial']) : '';
            $sucursal['razon_social'] = isset($sucursal['razon_social']) && $sucursal['razon_social'] ? Crypt::encrypt($sucursal['razon_social']) : '';
            $sucursal['correo'] = isset($sucursal['correo']) && $sucursal['correo'] ? Crypt::encrypt($sucursal['correo']) : '';
            $sucursal['telefono'] = isset($sucursal['telefono']) && $sucursal['telefono'] ? Crypt::encrypt($sucursal['telefono']) : '';

            return $sucursal;
        }

        if ($sucursal) {
            $sucursal->nombre_comercial = isset($sucursal->nombre_comercial) && $sucursal->nombre_comercial ? Crypt::encrypt($sucursal->nombre_comercial) : '';
            $sucursal->razon_social = isset($sucursal->razon_social) && $sucursal->razon_social ? Crypt::encrypt($sucursal->razon_social) : '';
            $sucursal->correo = isset($sucursal->correo) && $sucursal->correo ? Crypt::encrypt($sucursal->correo) : '';
            $sucursal->telefono = isset($sucursal->telefono) && $sucursal->telefono ? Crypt::encrypt($sucursal->telefono) : '';
        }

        return $sucursal;
    }

    public static function decryptInfo($sucursal)
    {
        if (is_array($sucursal)) {
            if (!isset($sucursal['decrypted']) || !$sucursal['decrypted']) {
                $sucursal['nombre_comercial'] = isset($sucursal['nombre_comercial']) && $sucursal['nombre_comercial'] ? Crypt::decrypt($sucursal['nombre_comercial']) : '';
                $sucursal['razon_social'] = isset($sucursal['razon_social']) && $sucursal['razon_social'] ? Crypt::decrypt($sucursal['razon_social']) : '';
                $sucursal['correo'] = isset($sucursal['correo']) && $sucursal['correo'] ? Crypt::decrypt($sucursal['correo']) : '';
                $sucursal['telefono'] = isset($sucursal['telefono']) && $sucursal['telefono'] ? Crypt::decrypt($sucursal['telefono']) : '';
                $sucursal['decrypted'] = true;
            }
            return $sucursal;
        }

        if ($sucursal && (!isset($sucursal->decrypted) || !$sucursal->decrypted)) {
            $sucursal->nombre_comercial = isset($sucursal->nombre_comercial) && $sucursal->nombre_comercial ? Crypt::decrypt($sucursal->nombre_comercial) : '';
            $sucursal->razon_social = isset($sucursal->razon_social) && $sucursal->razon_social ? Crypt::decrypt($sucursal->razon_social) : '';
            $sucursal->correo = isset($sucursal->correo) && $sucursal->correo ? Crypt::decrypt($sucursal->correo) : '';
            $sucursal->telefono = isset($sucursal->telefono) && $sucursal->telefono ? Crypt::decrypt($sucursal->telefono) : '';
            $sucursal->decrypted = true;
        }

        return $sucursal;
    }

    public static function parseData($data = [])
    {
        foreach ($data as $key => $value) {
            if (in_array($key, ['regimen_fiscal_id']) && $value) {
                switch ($key) {
                    case 'regimen_fiscal_id':
                        $data['regimen_fiscal'] = DB::table('tb_regimen_fiscales')
                            ->selectRaw('id, CONCAT(codigo, " - ", descripcion) as nombre')->where('id', $value)->first()->nombre;
                        break;
                }
            }
        }

        return $data;
    }

    public function getValueAttribute()
    {
        return $this->id;
    }

    public function getLabelAttribute()
    {
        return $this->nombre_comercial;
    }

    public function getDireccionTextAttribute()
    {
        $dir_text = '';
        if ($this->direccion_fiscal()->exists()) {
            $direccion = $this->direccion_fiscal()->get()->first();

            $dir_text .= $direccion->calle ? ('Calle: ' . $direccion->calle . '.') : '';
            $dir_text .= $direccion->no_exterior ? (' No. Exterior: ' . $direccion->no_exterior . '. ') : '';
            $dir_text .= $direccion->no_interior ? (' No. Interior: ' . $direccion->no_interior . '. ') : '';
            $dir_text .= $direccion->codigo_postal ? (' Código Postal: ' . $direccion->codigo_postal . '. ') : '';
            $dir_text .= $direccion->colonia ? (' Colonia: ' . $direccion->colonia . '. ') : '';
            $dir_text .= $direccion->localidad()->exists() ? (' Localidad: ' . $direccion->localidad()->first()->nombre . '. ') : '';
            $dir_text .= $direccion->municipio()->exists() ? (' Municipio: ' . $direccion->municipio()->first()->nombre . '. ') : '';
            $dir_text .= $direccion->estado()->exists() ? (' Estado: ' . $direccion->estado()->first()->nombre . '. ') : '';
            $dir_text .= $direccion->referencia ? (' Referencia: ' . $direccion->referencia . '.') : '';
        }
        return $dir_text;
    }

    public function getDireccionPlainAttribute()
    {
        $dir_text = '';
        if ($this->direccion_fiscal()->exists()) {
            $direccion = $this->direccion_fiscal()->get()->first();

            $dir_text .= $direccion->calle ? ($direccion->calle . '. ') : '';
            $dir_text .= $direccion->no_exterior ? ($direccion->no_exterior . '. ') : '';
            $dir_text .= $direccion->no_interior ? ($direccion->no_interior . '. ') : '';
            $dir_text .= $direccion->codigo_postal ? ('CP: ' . $direccion->codigo_postal . '. ') : '';
            $dir_text .= $direccion->colonia ? ($direccion->colonia . '. ') : '';
            $dir_text .= $direccion->localidad()->exists() ? ($direccion->localidad()->first()->nombre . '. ') : '';
            $dir_text .= $direccion->municipio()->exists() ? ($direccion->municipio()->first()->nombre . '. ') : '';
            $dir_text .= $direccion->estado()->exists() ? ($direccion->estado()->first()->nombre . '. ') : '';
            $dir_text .= $direccion->referencia ? ($direccion->referencia . '.') : '';
        }
        return $dir_text;
    }

    public function getLogoUriAttribute()
    {
        if ($this->logo && Storage::disk('logos')->exists($this->logo))
            return asset('logos/' . $this->logo);
        return '';
    }

    public function getCodigoPostalAttribute()
    {
        return $this->direccion_fiscal()->exists() ? $this->direccion_fiscal->codigo_postal : '';
    }

    public function direccion_fiscal()
    {
        return $this->belongsTo(Direccion::class)->withDefault([
            'calle' => '',
            'no_exterior' => '',
            'no_interior' => '',
            'codigo_postal' => '',
            'colonia' => '',
            'localidad_id' => null,
            'municipio_id' => null,
            'estado_id' => null,
            'referencia' => ''
        ]);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class)->withTrashed();
    }

    public function regimen_fiscal()
    {
        return $this->belongsTo(RegimenFiscal::class);
    }
}
