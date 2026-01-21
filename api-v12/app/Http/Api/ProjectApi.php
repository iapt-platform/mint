<?php

namespace App\Http\Api;

use App\Models\Project;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class ProjectApi
{
    public static function getById($id)
    {
        if (!$id) {
            return null;
        };
        $project = Project::find($id);
        if ($project) {
            return [
                'id' => $id,
                'sn' => $project->id,
                'title' => $project->title,
                'type' => $project->type,
                'weight' => $project->weight,
                'description' => $project->description,
            ];
        } else {
            return null;
        }
    }

    public static function getListByIds($ids)
    {
        if (!$ids) {
            return null;
        };
        $projects = Project::whereIn('uid', $ids)->get();
        $output = array();
        foreach ($ids as $key => $id) {
            foreach ($projects as $project) {
                if ($project->uid === $id) {
                    $output[] = [
                        'id' => $id,
                        'sn' => $project->id,
                        'title' => $project->title,
                        'type' => $project->type,
                        'weight' => $project->weight,
                        'description' => $project->description,
                    ];
                    continue;
                };
            }
        }
        return $output;
    }
}
