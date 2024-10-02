<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WordIndex extends Model
{
    use HasFactory;
	protected $fillable = ['id' , 'word' , 'word_en' , 'count' , 'normal' , 'bold' , 'is_base' , 'len'  ];

	
}
