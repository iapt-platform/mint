<?php

namespace App\Http\Controllers;

use App\Http\Resources\AiModelResource;
use App\Models\AiModel;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SysModelController extends Controller
{
    protected $key = '/ai/model/system/';

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), 401, 401);
        }
        $modelsId = Cache::get($this->key.$request->input('view', 'wbw'));
        if (! is_array($modelsId)) {
            $modelsId = [];
        }
        $result = AiModel::whereIn('uid', $modelsId)
            ->get();

        return $this->ok(
            [
                'rows' => AiModelResource::collection(resource: $result),
                'count' => count($result),
            ]
        );
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
        Cache::put(
            $this->key.$request->input('view', 'wbw'),
            $request->input('models')
        );

        return $this->ok('ok');
    }

    /**
     * Display the specified resource.
     *
     * @param  AiModel  $aiModel
     * @return Response
     */
    public function show(string $view)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, AiModel $aiModel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(AiModel $aiModel)
    {
        //
    }
}
