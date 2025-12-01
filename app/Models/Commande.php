<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_name',
        'client_email',
        'client_phone',
        'status',
        'total'
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    const STATUS_EN_ATTENTE = 'en_attente';
    const STATUS_CONFIRMEE = 'confirmee';
    const STATUS_ANNULEE = 'annulee';

    public static $statuses = [
        self::STATUS_EN_ATTENTE => 'En attente',
        self::STATUS_CONFIRMEE => 'Confirmée',
        self::STATUS_ANNULEE => 'Annulée'
    ];

    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'commande_produit')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
}