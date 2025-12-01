<?php
// app/Models/PageView.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_name',
        'ip_address',
        'user_agent',
         'session_id'
    ];
}