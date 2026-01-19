<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $casts = [
        'id' => 'string'
    ];
    protected $fillable = ['id', 'creator_id', 'owner_id', 'project_id'];

    protected $dates = [
        'created_at',
        'updated_at',
        'started_at',
        'finished_at',
    ];

    // app/Models/User.php
    public function task_assignees()
    {
        return $this->hasMany(TaskAssignee::class, 'task_id', 'id');
    }
}
