<?php

namespace App\Http\Controllers;

use App\Models\Relation;
use Illuminate\Http\Request;
use App\Http\Resources\RelationResource;
use App\Http\Api\AuthApi;
use Illuminate\Support\Facades\App;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Cache;

class RelationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $key = 'relation-vocabulary';
        if ($request->has('vocabulary')) {
            if (Cache::has($key)) {
                return $this->ok(Cache::get($key));
            }
        }
        $table = Relation::select([
            'id',
            'name',
            'case',
            'from',
            'to',
            'category',
            'editor_id',
            'match',
            'updated_at',
            'created_at'
        ]);
        if (($request->has('case'))) {
            $table = $table->whereIn('case', explode(",", $request->input('case')));
        }
        if (($request->has('search'))) {
            $table = $table->where('name', 'like', $request->input('search') . "%");
        }
        if (($request->has('name'))) {
            $table = $table->where('name', $request->input('name'));
        }
        if (($request->has('from'))) {
            $table = $table->whereJsonContains('from->case', $request->input('from'));
        }
        if (($request->has('to'))) {
            $table = $table->whereJsonContains('to', $request->input('to'));
        }
        if (($request->has('match'))) {
            $table = $table->whereJsonContains('match', $request->input('match'));
        }
        if (($request->has('category'))) {
            $table = $table->where('category', $request->input('category'));
        }
        $table = $table->orderBy($request->input('order', 'updated_at'), $request->input('dir', 'desc'));
        $count = $table->count();

        $table = $table->skip($request->input("offset", 0))
            ->take($request->input('limit', 1000));
        $result = $table->get();

        $output = ["rows" => RelationResource::collection($result), "count" => $count];

        if ($request->has('vocabulary')) {
            if (!Cache::has($key)) {
                Cache::put($key, $output, config('mint.cache.expire'));
            }
        }
        return $this->ok($output);
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
        $user = AuthApi::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'), [], 401);
        }
        //TODO 判断权限
        $validated = $request->validate([
            'name' => 'required',
        ]);
        $case = $request->input('case', '');
        $new = new Relation;
        $new->name = $validated['name'];

        $new->case = $request->input('case');
        $new->category = $request->input('category');

        if ($request->has('from')) {
            $new->from = json_encode($request->input('from'), JSON_UNESCAPED_UNICODE);
        } else {
            $new->from = null;
        }
        if ($request->has('to')) {
            $new->to = json_encode($request->input('to'), JSON_UNESCAPED_UNICODE);
        } else {
            $new->to = null;
        }
        if ($request->has('match')) {
            $new->match = json_encode($request->input('match'), JSON_UNESCAPED_UNICODE);
        } else {
            $new->match = null;
        }
        $new->editor_id = $user['user_uid'];
        $new->save();
        return $this->ok(new RelationResource($new));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Relation  $relation
     * @return \Illuminate\Http\Response
     */
    public function show(Relation $relation)
    {
        //
        return $this->ok(new RelationResource($relation));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Relation  $relation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Relation $relation)
    {
        //
        $user = AuthApi::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'));
        }

        $relation->name = $request->input('name');
        $relation->case = $request->input('case');
        $relation->category = $request->input('category');

        if ($request->has('from')) {
            $relation->from = json_encode($request->input('from'), JSON_UNESCAPED_UNICODE);
        } else {
            $relation->from = null;
        }
        if ($request->has('to')) {
            $relation->to = json_encode($request->input('to'), JSON_UNESCAPED_UNICODE);
        } else {
            $relation->to = null;
        }
        if ($request->has('match')) {
            $relation->match = json_encode($request->input('match'), JSON_UNESCAPED_UNICODE);
        } else {
            $relation->match = null;
        }
        $relation->editor_id = $user['user_uid'];
        $relation->save();
        return $this->ok(new RelationResource($relation));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Relation  $relation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Relation $relation)
    {
        //
        $user = AuthApi::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'));
        }
        //TODO 判断当前用户是否有权限
        $delete = 0;
        $delete = $relation->delete();

        return $this->ok($delete);
    }

    public function export()
    {
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $activeWorksheet->setCellValue('A1', 'id');
        $activeWorksheet->setCellValue('B1', 'name');
        $activeWorksheet->setCellValue('C1', 'from');
        $activeWorksheet->setCellValue('D1', 'to');
        $activeWorksheet->setCellValue('E1', 'match');
        $activeWorksheet->setCellValue('F1', 'category');

        $nissaya = Relation::cursor();
        $currLine = 2;
        foreach ($nissaya as $key => $row) {
            # code...
            $activeWorksheet->setCellValue("A{$currLine}", $row->id);
            $activeWorksheet->setCellValue("B{$currLine}", $row->name);
            $activeWorksheet->setCellValue("C{$currLine}", $row->from);
            $activeWorksheet->setCellValue("D{$currLine}", $row->to);
            $activeWorksheet->setCellValue("E{$currLine}", $row->match);
            $activeWorksheet->setCellValue("F{$currLine}", $row->category);
            $currLine++;
        }
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="relation.xlsx"');
        $writer->save("php://output");
    }

    public function import(Request $request)
    {
        $user = AuthApi::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'));
        }

        $filename = $request->input('filename');
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filename);
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $currLine = 2;
        $countFail = 0;
        $error = "";
        do {
            # code...
            $id = $activeWorksheet->getCell("A{$currLine}")->getValue();
            $name = $activeWorksheet->getCell("B{$currLine}")->getValue();
            $from = $activeWorksheet->getCell("C{$currLine}")->getValue();
            $to = $activeWorksheet->getCell("D{$currLine}")->getValue();
            $match = $activeWorksheet->getCell("E{$currLine}")->getValue();
            $category = $activeWorksheet->getCell("F{$currLine}")->getValue();
            if (!empty($name)) {
                //查询是否有冲突数据
                //查询此id是否有旧数据
                if (!empty($id)) {
                    $oldRow = Relation::find($id);
                }
                //查询是否跟已有数据重复
                /*
                $row = Relation::where(['name'=>$name,
                                        'from'=>json_decode($from,true),
                                        'to'=>$to,
                                        'match'=>$match,
                                        'category'=>$category
                                        ])->first();
                */
                $row = false;
                if (!$row) {
                    //不重复
                    if (isset($oldRow) && $oldRow) {
                        //有旧的记录-修改旧数据
                        $row = $oldRow;
                    } else {
                        //没找到旧的记录-新建
                        $row = new Relation();
                    }
                } else {
                    //重复-如果与旧的id不同旧报错
                    if (isset($oldRow) && $oldRow && $row->id !== $id) {
                        $error .= "重复的数据：id={$id} -\n";
                        $currLine++;
                        $countFail++;
                        continue;
                    }
                }
                $row->name = $name;
                if (empty($from)) {
                    $row->from = null;
                } else {
                    $row->from = $from;
                }
                $row->to = $to;
                $row->match = $match;
                $row->category = $category;
                $row->editor_id = $user['user_uid'];
                $row->save();
            } else {
                break;
            }
            $currLine++;
        } while (true);
        return $this->ok(["success" => $currLine - 2 - $countFail, 'fail' => ($countFail)], $error);
    }
}
