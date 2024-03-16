<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sentence extends Model
{
    use HasFactory;
    use SoftDeletes;
	protected $fillable = ['id','uid','book_id',
                          'paragraph','word_start','word_end',
                          'channel_uid','editor_uid','content','content_type',
                          'strlen','status','create_time','modify_time','language'];
    protected $primaryKey = 'uid';
    protected $casts = [
        'uid' => 'string'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'fork_at'
    ];

}
