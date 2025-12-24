<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

class Clothes extends Model
{
    use Filterable;
    

    protected $fillable = ['name', 'image', 'code'];
}
