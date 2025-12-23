<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = [
        'type',
        'image',
        'color_code',
        'code',
        'name',
        'measurement'        
    ];
}
