<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDict extends Model
{
    use HasFactory;
	protected $casts = [
		'id' => 'string'
	];
	protected $fillable = ['id','word','type','grammar','parent','mean','note','factors','factor_mean','source','create_time','creator_id','dict_id'];
}
