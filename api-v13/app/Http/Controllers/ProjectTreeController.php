<?php

namespace App\Http\Controllers;

use App\Http\Api\StudioApi;
use App\Models\Project;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ProjectTreeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), 401, 401);
        }
        $studioId = StudioApi::getIdByName($request->input('studio_name'));
        if (! ProjectController::canEdit($user['user_uid'], $studioId)) {
            return $this->error(__('auth.failed'), 403, 403);
        }
        $newData = [];
        foreach ($request->input('data') as $key => $value) {
            $data = [
                'uid' => Str::uuid(),
                'old_id' => $value['id'],
                'title' => $value['title'],
                'type' => $value['type'],
                'res_id' => $value['res_id'],
                'parent_id' => $value['parent_id'],
                'path' => null,
                'owner_id' => $studioId,
                'editor_id' => $user['user_uid'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (isset($value['weight'])) {
                $data['weight'] = $value['weight'];
            }
            $newData[] = $data;
        }
        foreach ($newData as $key => $value) {
            if ($value['parent_id']) {
                $parent = null;
                /*
                $parent = \array_find($newData, function ($element) use ($value) {
                    return $element['old_id'] == $value['parent_id'];
                });
                */
                foreach ($newData as $item) {
                    if ($item['old_id'] == $value['parent_id']) {
                        $parent = $item;
                        break;
                    }
                }
                if ($parent) {
                    $newData[$key]['parent_id'] = $parent['uid'];
                    $parentPath = $parent['path'] ? json_decode($parent['path']) : [];
                    $newData[$key]['path'] = json_encode([...$parentPath, $parent['uid']], JSON_UNESCAPED_UNICODE);
                } else {
                    $newData[$key]['parent_id'] = null;
                }
            } elseif (! empty($request->input('parent_id'))) {
                $pPath = Project::where('uid', $request->input('parent_id'))->value('path');
                $parentPath = json_decode($pPath);
                if (! is_array($parentPath)) {
                    $parentPath = [];
                }
                $newData[$key]['path'] = json_encode([...$parentPath, $request->input('parent_id')], JSON_UNESCAPED_UNICODE);
                $newData[$key]['parent_id'] = $request->input('parent_id');
            }
        }
        $output = [];
        foreach ($newData as $key => $value) {
            $children = array_filter($newData, function ($element) use ($value) {
                return $element['parent_id'] === $value['uid'];
            });
            $output[] = [
                'id' => $value['uid'],
                'resId' => $value['res_id'],
                'isLeaf' => count($children) === 0,
            ];
            unset($newData[$key]['old_id']);
            unset($newData[$key]['res_id']);
        }

        $ok = Project::insert($newData);

        if ($ok) {
            return $this->ok(['rows' => $output, count($output)]);
        } else {
            return $this->error('error', 200, 200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Project $project)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, Project $project)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Project $project)
    {
        //
    }
}
