<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DhammaTerm extends Model
{
    use HasFactory;

    protected $primaryKey = 'guid';
    protected $casts = [
        'guid' => 'string'
    ];
    public  $incrementing = false;
	protected $fillable = ['id' , 'guid', 'word','word_en','meaning','channal','language','owner','editor_id','create_time','modify_time'];

}
