<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
protected $fillable = [
    'name',
    'email',
    'password',
    'two_factor_secret',
    'two_factor_confirmed_at',
    'two_factor_last_verified_at',
    'rol',
    'rut',
    'telefono',
    'activo',
    'vendedor_id',
    'profesional_id',
    'login_otp_hash',
    'login_otp_expira',
    'login_otp_validado_at',
];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'two_factor_last_verified_at' => 'datetime',
        'login_otp_expira' => 'datetime',
        'login_otp_validado_at' => 'datetime',
        'activo' => 'boolean',
    ];

    public function tieneRol(...$roles)
    {
        return in_array($this->rol, $roles, true);
    }
}
