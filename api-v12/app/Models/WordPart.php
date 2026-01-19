<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WordPart extends Model
{
    use HasFactory;
	protected $fillable = ['word' , 'weight'];

}
