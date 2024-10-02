<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaliText extends Model
{
    use HasFactory;
	protected $fillable = ['book','paragraph','level','class','toc','text','html','lenght'];
}
