<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

use App\Models\UserDict;
use App\Models\WordIndex;
use App\Http\Resources\DictPreferenceResource;
use App\Http\Api\DictApi;
use App\Http\Api\AuthApi;

class DictPreferenceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $dict_id = DictApi::getSysDict('system_preference');
        if (!$dict_id) {
            return $this->error('没有找到 system_preference 字典', 200, 200);
        }
        $table = WordIndex::where('user_dicts.dict_id', $dict_id)
            ->leftJoin('user_dicts', 'word_indices.word', '=', 'user_dicts.word')
            ->select([
                'user_dicts.id',
                'word_indices.word',
                'word_indices.count',
                'user_dicts.factors',
                'user_dicts.parent',
                'user_dicts.note',
                'user_dicts.confidence',
                'user_dicts.editor_id',
            ]);
        //处理搜索
        if (!empty($request->get("keyword"))) {
            $table = $table->where('word_indices.word', 'like', "%" . $request->get("keyword") . "%");
        }

        //获取记录总条数
        $count = $table->count();
        //处理排序
        $table = $table->orderBy(
            $request->get("order", 'word_indices.count'),
            $request->get("dir", 'desc')
        );
        //处理分页
        $table = $table->skip($request->get("offset", 0))
            ->take($request->get("limit", 200));
        //获取数据
        $result = $table->get();
        return $this->ok([
            "rows" => DictPreferenceResource::collection($result),
            "count" => $count
        ]);
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
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserDict  $userDict
     * @return \Illuminate\Http\Response
     */
    public function show(UserDict $userDict)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,  $id)
    {
        $user = AuthApi::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'), [], 401);
        }
        $newData = $request->all();
        $word = UserDict::findOrFail($id);
        if (isset($newData['factors'])) {
            $word->factors = $newData['factors'];
        }
        if (isset($newData['parent'])) {
            $word->parent = $newData['parent'];
        }
        if (isset($newData['confidence'])) {
            $word->confidence = $newData['confidence'];
        }
        $word->editor_id = $user['user_uid'];
        $word->save();
        return $this->ok(new DictPreferenceResource($word));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserDict  $userDict
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserDict $userDict)
    {
        //
    }
}
