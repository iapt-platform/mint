<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DictInfo extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    public  $incrementing = false;
	protected $casts = [
		'id' => 'string'
	];
	protected $fillable = ['id' , 'name', 'shortname','src_lang','dest_lang','rows','owner_id'];

}
