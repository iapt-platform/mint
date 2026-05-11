<?php

namespace App\Http\Controllers;

use App\Models\ModelLog;
use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Http\Resources\ModelLogResource;
use Illuminate\Support\Str;

class ModelLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $user = AuthService::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'));
        }
        switch ($request->input('view')) {
            case 'model':
                # code..
                $table = ModelLog::where('model_id', $request->input('id'));
                break;

            default:
                # code...
                break;
        }
        if ($request->has('search')) {
            $table = $table->where('email', 'like', '%' . $request->input('search') . "%");
        }
        $count = $table->count();
        $table = $table->orderBy(
            $request->input('order', 'updated_at'),
            $request->input('dir', 'desc')
        );

        $table = $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 20));

        $result = $table->get();
        return $this->ok(["rows" => ModelLogResource::collection($result), "total" => $count]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $modelLog = new ModelLog();
        $modelLog->uid = Str::uuid();
        $modelLog->model_id = $request->input('model_id');
        $modelLog->request_at = $request->input('request_at');
        $modelLog->request_headers = $request->input('request_headers');
        $modelLog->request_data = $request->input('request_data');
        $modelLog->response_headers = $request->input('response_headers');
        $modelLog->response_data = $request->input('response_data');
        $modelLog->status = $request->input('status');
        $modelLog->success = $request->input('success', true);
        $modelLog->save();
        return $this->ok(new ModelLogResource($modelLog));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ModelLog  $modelLog
     * @return \Illuminate\Http\Response
     */
    public function show(ModelLog $modelLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ModelLog  $modelLog
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ModelLog $modelLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ModelLog  $modelLog
     * @return \Illuminate\Http\Response
     */
    public function destroy(ModelLog $modelLog)
    {
        //
    }
}
