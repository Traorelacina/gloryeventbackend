<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image',
        'category',
        'featured',
        'date'
    ];

    protected $casts = [
        'featured' => 'boolean',
        'date' => 'date'
    ];

    const CATEGORIES = [
        'mariage',
        'corporate',
        'anniversaire',
        'evenement_professionnel'
    ];

    
    // Relation avec les images supplémentaires
    public function images()
    {
        return $this->hasMany(PortfolioImage::class)->orderBy('order');
    }
}
