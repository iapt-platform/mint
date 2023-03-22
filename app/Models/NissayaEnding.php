<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NissayaEnding extends Model
{
    use HasFactory;
    protected $casts = [
        'id' => 'string'
    ];
	protected $fillable = ['id','ending','lang','relation'];

}
