<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'image',
        'category',
        'in_stock',
        'featured'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'in_stock' => 'boolean',
        'featured' => 'boolean',
    ];

    public function commandes()
    {
        return $this->belongsToMany(Commande::class, 'commande_produit')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
}