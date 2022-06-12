<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DhammaTerm extends Model
{
    use HasFactory;

    protected $primaryKey = 'guid';
    public  $incrementing = false;
    //protected $keyType = "string";
    protected $casts = [
        'guid' => 'string'
    ];
}
