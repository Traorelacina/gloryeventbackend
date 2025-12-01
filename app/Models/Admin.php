<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    const ROLE_ADMIN = 'admin';
    const ROLE_EDITOR = 'editor';

    public static $roles = [
        self::ROLE_ADMIN => 'Administrateur',
        self::ROLE_EDITOR => 'Éditeur'
    ];

    /**
     * Vérifie si l'admin a le rôle administrateur
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Vérifie si l'admin a le rôle éditeur
     */
    public function isEditor(): bool
    {
        return $this->role === self::ROLE_EDITOR;
    }

    /**
     * Vérifie si l'admin est actif
     */
    public function isActive(): bool
    {
        return $this->is_active ?? true;
    }
}
