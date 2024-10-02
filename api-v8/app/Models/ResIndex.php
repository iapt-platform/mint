<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResIndex extends Model
{
    use HasFactory;
	protected $fillable = ['book','paragraph','title','title_en','level','type','language','author','share','create_time','update_time'];

}
