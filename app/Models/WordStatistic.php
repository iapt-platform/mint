<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WordStatistic extends Model
{
    use HasFactory;
	protected $fillable = ['bookid','word' , 'count', 'base' , 'end1' , 'end2','type', 'length'];

}
