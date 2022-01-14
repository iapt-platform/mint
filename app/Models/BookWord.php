<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookWord extends Model
{
    use HasFactory;
	protected $fillable = ['book' , 'wordindex', 'count'];

}
