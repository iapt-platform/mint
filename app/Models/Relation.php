<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Relation extends Model
{
    use HasFactory;
    protected $casts = [
        'id' => 'string'
    ];
	protected $fillable = ['id','name','case','to'];

}
