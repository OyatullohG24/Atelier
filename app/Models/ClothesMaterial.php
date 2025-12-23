<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClothesMaterial extends Model
{
    protected $fillable = ['clothes_id', 'material_id', 'amount'];

    public function clothes()
    {
        return $this->belongsTo(Clothes::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
