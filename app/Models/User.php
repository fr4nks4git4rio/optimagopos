<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Administracion\CodificadoresGenerales\Operador;
use App\Models\Administracion\Rol;
use App\Models\Ventas\OrdenServicio;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Class User
 *
 * @property string $nombre
 * @property string $apellidos
 * @property string $email
 * @property string $password
 * @property string $avatar
 * @property integer $rol_id
 * @property integer $cliente_id
 */
class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, CausesActivity, SoftDeletes;

    protected $table = 'tb_usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre',
        'apellidos',
        'telefono',
        'email',
        'password',
        'avatar',
        'rol_id',
        'cliente_id',
        // 'two_factor_authentication_enabled',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        // 'two_factor_authentication_enabled'
    ];

    protected $appends = ['nombre_completo', 'is_super_admin', 'is_admin'];

    public function rules()
    {
        return [
            'email' => ['required', 'email', Rule::unique('tb_usuarios')->ignore($this->id)],
            'nombre' => ['required'],
            'apellidos' => ['nullable'],
            'rol_id' => ['required'],
            'cliente_id' => ['nullable', 'required_if:rol_id,2', 'exists:tb_clientes,id'],
            'password' => [Rule::requiredIf(function () {
                return $this->id == null;
            }), 'confirmed']
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Campo requerido.',
            'email.email' => 'Formato de correo incorrecto.',
            'email.unique' => 'Ya existe el correo.',
            'nombre.required' => 'Campo requerido.',
            'rol_id.required' => 'Campo requerido.',
            'cliente_id.required_if' => 'Campo requerido.',
            'cliente_id.exists' => 'Cliente no encontrado.',
            'password.required' => 'Campo requerido.',
            'password.confirmed' => 'La contraseña no coincide con su confirmación.'
        ];
    }

    public function twoFactorAuthenticationEnabled()
    {
        return $this->two_factor_authentication_enabled;
    }

    public function twoFactorAuthenticationVerified($ip)
    {
        return !$this->two_factor_authentication_enabled || $this->last_authenticated_ip == $ip;
    }
    public function getAvatarUriAttribute()
    {
        if ($this->avatar && Storage::disk('avatars')->exists($this->avatar))
            //            return Storage::disk('avatars')->url($this->avatar);
            return asset('avatars/' . $this->avatar);
        return '';
    }
    public function getNombreCompletoAttribute()
    {
        return $this->nombre . ' ' . $this->apellidos;
    }

    public function getIsSuperAdminAttribute()
    {
        return $this->rol_id == 1;
    }

    public function getIsAdminAttribute()
    {
        return $this->rol_id == 2;
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public static function parseData($data = [])
    {
        foreach ($data as $key => $value) {
            if (in_array($key, ['cliente_id', 'rol_id']) && $value) {
                switch ($key) {
                    case 'cliente_id':
                        $data['cliente'] = DB::table('tb_clientes')
                            ->selectRaw('id, nombre_comercial as nombre')->where('id', $value)->first()->nombre;
                        break;
                    case 'rol_id':
                        $data['rol'] = DB::table('tb_roles')
                            ->selectRaw('id, nombre')->where('id', $value)->first()->nombre;
                        break;
                }
            }
        }

        return $data;
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function rol()
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }

    public function owner()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
