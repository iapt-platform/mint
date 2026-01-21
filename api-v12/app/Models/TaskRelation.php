<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskRelation extends Model
{
    use HasFactory;
    protected $fillable = ['id','task_id','next_task_id','editor_id'];

}
