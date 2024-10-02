<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WordList extends Model
{
    use HasFactory;
	protected $fillable = ['sn','book' , 'paragraph', 'wordindex' , 'bold' , 'weight' ];

}
