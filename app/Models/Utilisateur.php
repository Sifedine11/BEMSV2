<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Utilisateur extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'utilisateurs';

    // Breeze/Guard vont utiliser 'email' + getAuthPassword()
    protected $fillable = [
        'nom_complet',
        'email',
        'mot_de_passe',
        'tel_mobile',
        'tel_fixe',
        'role',
        'actif',
    ];

    protected $hidden = [
        'mot_de_passe',
        'remember_token',
    ];

    protected $casts = [
        'email_verifie_le' => 'datetime',
        'actif' => 'boolean',
    ];

    // ✅ Très important : indique à Laravel où se trouve le password
    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    // Helper rôle(s)
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    // Pour compat vue Breeze qui affiche "name"
    public function getNameAttribute(): string
    {
        return $this->nom_complet ?? $this->email;
    }
}
