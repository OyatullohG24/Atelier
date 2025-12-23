<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Storage extends Model
{
    protected $fillable = [
        'work_amount',
        'price',
        'code',
        'color_code',
        'come_amount',
        'measurement'
    ];
}
