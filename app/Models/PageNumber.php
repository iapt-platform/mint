<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageNumber extends Model
{
    use HasFactory;
	protected $fillable = ['type','volume','page',
                            'book','paragraph','wid','pcd_book_id'];

}
