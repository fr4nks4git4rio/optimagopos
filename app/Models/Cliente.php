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
 * Class Cliente
 * @package App\Models
 * @version October 6, 2019, 12:01 pm CDT
 *
 * @property string $nombre_comercial
 * @property string $razon_social
 * @property string $rfc
 * @property string $correo
 * @property string $telefono
 * @property string $contacto_nombre
 * @property string $contacto_correo
 * @property string $contacto_telefono
 * @property string $contacto_cargo
 * @property boolean $es_comensal
 * @property boolean $es_cliente
 * @property boolean $es_propietario
 * @property boolean $es_cliente_fiel
 * @property string $comentarios
 * @property boolean $con_facturacion
 * @property string $logo
 * @property string $portal_pac
 * @property string $usuario_integrador_sat
 * @property integer $direccion_fiscal_id
 * @property integer $regimen_fiscal_id
 */
class Cliente extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    public $table = 'tb_clientes';

    public $fillable = [
        'nombre_comercial',
        'razon_social',
        'rfc',
        'correo',
        'telefono',
        'contacto_nombre',
        'contacto_correo',
        'contacto_telefono',
        'contacto_cargo',
        'prefijo',
        'es_comensal',
        'es_cliente',
        'es_propietario',
        'es_cliente_fiel',
        'comentarios',
        'con_facturacion',
        'logo',
        'portal_pac',
        'usuario_integrador_sat',
        'direccion_fiscal_id',
        'regimen_fiscal_id'
    ];

    protected $appends = ['value', 'label', 'text', 'direccion_text', 'direccion_plain', 'logo_uri', 'codigo_postal'];

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
        'prefijo' => 'string',
        'es_comensal' => 'boolean',
        'es_cliente' => 'boolean',
        'es_propietario' => 'boolean',
        'es_cliente_fiel' => 'boolean',
        'comentarios' => 'string',
        'con_facturacion' => 'boolean',
        'portal_pac' => 'string',
        'usuario_integrador_sat' => 'string',
        'direccion_fiscal_id' => 'integer',
        'regimen_fiscal_id' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public function rules() {}

    public function messages() {}

    public static function encryptInfo($cliente)
    {
        if (is_array($cliente)) {
            $cliente['nombre_comercial'] = isset($cliente['nombre_comercial']) && $cliente['nombre_comercial'] ? Crypt::encrypt($cliente['nombre_comercial']) : '';
            $cliente['razon_social'] = isset($cliente['razon_social']) && $cliente['razon_social'] ? Crypt::encrypt($cliente['razon_social']) : '';
            $cliente['correo'] = isset($cliente['correo']) && $cliente['correo'] ? Crypt::encrypt($cliente['correo']) : '';
            $cliente['telefono'] = isset($cliente['telefono']) && $cliente['telefono'] ? Crypt::encrypt($cliente['telefono']) : '';
            $cliente['contacto_nombre'] = isset($cliente['contacto_nombre']) && $cliente['contacto_nombre'] ? Crypt::encrypt($cliente['contacto_nombre']) : '';
            $cliente['contacto_correo'] = isset($cliente['contacto_correo']) && $cliente['contacto_correo'] ? Crypt::encrypt($cliente['contacto_correo']) : '';
            $cliente['contacto_telefono'] = isset($cliente['contacto_telefono']) && $cliente['contacto_telefono'] ? Crypt::encrypt($cliente['contacto_telefono']) : '';
            $cliente['contacto_cargo'] = isset($cliente['contacto_cargo']) && $cliente['contacto_cargo'] ? Crypt::encrypt($cliente['contacto_cargo']) : '';

            return $cliente;
        }

        if ($cliente) {
            $cliente->nombre_comercial = isset($cliente->nombre_comercial) && $cliente->nombre_comercial ? Crypt::encrypt($cliente->nombre_comercial) : '';
            $cliente->razon_social = isset($cliente->razon_social) && $cliente->razon_social ? Crypt::encrypt($cliente->razon_social) : '';
            $cliente->correo = isset($cliente->correo) && $cliente->correo ? Crypt::encrypt($cliente->correo) : '';
            $cliente->telefono = isset($cliente->telefono) && $cliente->telefono ? Crypt::encrypt($cliente->telefono) : '';
            $cliente->contacto_nombre = isset($cliente->contacto_nombre) && $cliente->contacto_nombre ? Crypt::encrypt($cliente->contacto_nombre) : '';
            $cliente->contacto_correo = isset($cliente->contacto_correo) && $cliente->contacto_correo ? Crypt::encrypt($cliente->contacto_correo) : '';
            $cliente->contacto_telefono = isset($cliente->contacto_telefono) && $cliente->contacto_telefono ? Crypt::encrypt($cliente->contacto_telefono) : '';
            $cliente->contacto_cargo = isset($cliente->contacto_cargo) && $cliente->contacto_cargo ? Crypt::encrypt($cliente->contacto_cargo) : '';
        }

        return $cliente;
    }

    public static function decryptInfo($cliente)
    {
        if (is_array($cliente)) {
            if (!isset($cliente['decrypted']) || !$cliente['decrypted']) {
                $cliente['nombre_comercial'] = isset($cliente['nombre_comercial']) && $cliente['nombre_comercial'] ? Crypt::decrypt($cliente['nombre_comercial']) : '';
                $cliente['razon_social'] = isset($cliente['razon_social']) && $cliente['razon_social'] ? Crypt::decrypt($cliente['razon_social']) : '';
                $cliente['correo'] = isset($cliente['correo']) && $cliente['correo'] ? Crypt::decrypt($cliente['correo']) : '';
                $cliente['telefono'] = isset($cliente['telefono']) && $cliente['telefono'] ? Crypt::decrypt($cliente['telefono']) : '';
                $cliente['contacto_nombre'] = isset($cliente['contacto_nombre']) && $cliente['contacto_nombre'] ? Crypt::decrypt($cliente['contacto_nombre']) : '';
                $cliente['contacto_correo'] = isset($cliente['contacto_correo']) && $cliente['contacto_correo'] ? Crypt::decrypt($cliente['contacto_correo']) : '';
                $cliente['contacto_telefono'] = isset($cliente['contacto_telefono']) && $cliente['contacto_telefono'] ? Crypt::decrypt($cliente['contacto_telefono']) : '';
                $cliente['contacto_cargo'] = isset($cliente['contacto_cargo']) && $cliente['contacto_cargo'] ? Crypt::decrypt($cliente['contacto_cargo']) : '';
                $cliente['decrypted'] = true;
            }
            return $cliente;
        }

        if ($cliente && (!isset($cliente->decrypted) || !$cliente->decrypted)) {
            $cliente->nombre_comercial = isset($cliente->nombre_comercial) && $cliente->nombre_comercial ? Crypt::decrypt($cliente->nombre_comercial) : '';
            $cliente->razon_social = isset($cliente->razon_social) && $cliente->razon_social ? Crypt::decrypt($cliente->razon_social) : '';
            $cliente->correo = isset($cliente->correo) && $cliente->correo ? Crypt::decrypt($cliente->correo) : '';
            $cliente->telefono = isset($cliente->telefono) && $cliente->telefono ? Crypt::decrypt($cliente->telefono) : '';
            $cliente->contacto_nombre = isset($cliente->contacto_nombre) && $cliente->contacto_nombre ? Crypt::decrypt($cliente->contacto_nombre) : '';
            $cliente->contacto_correo = isset($cliente->contacto_correo) && $cliente->contacto_correo ? Crypt::decrypt($cliente->contacto_correo) : '';
            $cliente->contacto_telefono = isset($cliente->contacto_telefono) && $cliente->contacto_telefono ? Crypt::decrypt($cliente->contacto_telefono) : '';
            $cliente->contacto_cargo = isset($cliente->contacto_cargo) && $cliente->contacto_cargo ? Crypt::decrypt($cliente->contacto_cargo) : '';
            $cliente->decrypted = true;
        }

        return $cliente;
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
    public function getTextAttribute()
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

    public function regimen_fiscal()
    {
        return $this->belongsTo(RegimenFiscal::class);
    }

    public function suscripciones()
    {
        return $this->hasMany(Suscripcion::class, 'cliente_id');
    }
    public function suscripciones_activa()
    {
        return $this->hasOne(Suscripcion::class, 'cliente_id')->where('estado', 'ACTIVA');
    }
    public function suscripcion_pendiente()
    {
        return $this->hasOne(Suscripcion::class, 'cliente_id')->where('estado', 'PENDIENTE');
    }

    public function comensales()
    {
        return $this->belongsToMany(Cliente::class, 'tb_clientes_comensales', 'cliente_id', 'comensal_id');
    }
    public function sucursales()
    {
        return $this->hasMany(Sucursal::class, 'cliente_id');
    }
}
