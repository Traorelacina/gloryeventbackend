<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'image',
        'video',
        'price_range',
        'duration',
        'featured',
        'category',
        'sous_category'
    ];

    protected $casts = [
        'featured' => 'boolean',
    ];

    // Catégories disponibles
    const CATEGORIES = [
        'evenementiel',
        'evenementiel_mariage', 
        'vetodiseurs',
        'historique',
        'relations_professionnelle',
        'documents',
        'reductions'
    ];

    const SOUS_CATEGORIES = [
        'quinze_elections',
        'quinze_etendances',
        'quinze_anneeuses'
    ];
}