<?php

namespace App\Http\Controllers;

use App\Http\Resources\DictVocabularyResource;
use App\Models\DictInfo;
use App\Models\UserDict;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DictVocabularyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->input('view')) {
            case 'dict_name':
                $id = DictInfo::where('name', $request->input('name'))->value('id');
                if (! $id) {
                    return $this->error('name:'.$request->input('name').' can not found.', 200, 200);
                }
                $table = UserDict::where('dict_id', $id)
                    ->groupBy('word')
                    ->selectRaw('word,count(*)');
                break;
            case 'dict_short_name':
                $id = DictInfo::where('shortname', $request->input('name'))->value('id');
                if (! $id) {
                    return $this->error('name:'.$request->input('name').' can not found.', 200, 200);
                }
                $table = UserDict::where('dict_id', $id)
                    ->groupBy('word')
                    ->selectRaw('word,count(*)');
                break;
        }
        if ($request->input('stream') === 'true') {
            return response()->streamDownload(function () use ($table) {
                $result = $table->get();
                echo json_encode($result);
            }, 'dict.txt');
        }
        $count = 2;
        $table = $table->orderBy('word', $request->input('dir', 'asc'));

        $table = $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 1000));

        $result = $table->get();

        return $this->ok([
            'rows' => DictVocabularyResource::collection($result),
            'count' => $count,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(UserDict $userDict)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, UserDict $userDict)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(UserDict $userDict)
    {
        //
    }
}
