<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{

    protected $keyType = 'string';
	protected $fillable = ['name' , 'owner_id'];

    use HasFactory;

    public function chapters()
    {
        return $this->belongsToMany('App\Models\ProgressChapter','tag_maps','tag_id','anchor_id');
    }
}
