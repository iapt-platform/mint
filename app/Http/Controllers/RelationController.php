<?php

namespace App\Http\Controllers;

use App\Models\Relation;
use Illuminate\Http\Request;
use App\Http\Resources\RelationResource;
use App\Http\Api\AuthApi;
use Illuminate\Support\Facades\App;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
        $table = Relation::select(['id','name','case','to','editor_id','updated_at','created_at']);
        if(($request->has('case'))){
            $table->whereIn('case', explode(",",$request->get('case')) );
        }
        if(($request->has('search'))){
            $table->where('name', 'like', $request->get('search')."%");
        }
        if(!empty($request->get('order')) && !empty($request->get('dir'))){
            $table->orderBy($request->get('order'),$request->get('dir'));
        }else{
            $table->orderBy('updated_at','desc');
        }
        $count = $table->count();
        if(!empty($request->get('limit'))){
            $offset = 0;
            if(!empty($request->get("offset"))){
                $offset = $request->get("offset");
            }
            $table->skip($offset)->take($request->get('limit'));
        }
        $result = $table->get();

		if($result){
			return $this->ok(["rows"=>RelationResource::collection($result),"count"=>$count]);
		}else{
			return $this->error("没有查询到数据");
		}
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
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //TODO 判断权限
        $validated = $request->validate([
            'name' => 'required',
        ]);
        $case = $request->get('case','');
        $new = new Relation;
        $new->name = $validated['name'];
        if($request->has('case')){
            $new->case = $request->get('case');
        }else{
            $new->case = null;
        }
        if($request->has('to')){
            $new->to = json_encode($request->get('to'),JSON_UNESCAPED_UNICODE);
        }else{
            $new->to = null;
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
        if(!$user){
            return $this->error(__('auth.failed'));
        }

        $relation->name = $request->get('name');
        if($request->has('case')){
            $relation->case = $request->get('case');
        }else{
            $relation->case = null;
        }
        if($request->has('to')){
            $relation->to = json_encode($request->get('to'),JSON_UNESCAPED_UNICODE);
        }else{
            $relation->to = null;
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
    public function destroy(Request $request,Relation $relation)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //TODO 判断当前用户是否有权限
        $delete = 0;
        $delete = $relation->delete();

        return $this->ok($delete);
    }

    public function export(){
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $activeWorksheet->setCellValue('A1', 'id');
        $activeWorksheet->setCellValue('B1', 'name');
        $activeWorksheet->setCellValue('C1', 'case');
        $activeWorksheet->setCellValue('D1', 'to');

        $nissaya = Relation::cursor();
        $currLine = 2;
        foreach ($nissaya as $key => $row) {
            # code...
            $activeWorksheet->setCellValue("A{$currLine}", $row->id);
            $activeWorksheet->setCellValue("B{$currLine}", $row->name);
            $activeWorksheet->setCellValue("C{$currLine}", $row->case);
            $activeWorksheet->setCellValue("D{$currLine}", $row->to);
            $currLine++;
        }
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="relation.xlsx"');
        $writer->save("php://output");
    }

    public function import(Request $request){
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }

        $filename = $request->get('filename');
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
            $case = $activeWorksheet->getCell("C{$currLine}")->getValue();
            $to = $activeWorksheet->getCell("D{$currLine}")->getValue();
            if(!empty($name)){
                                //查询是否有冲突数据
                //查询此id是否有旧数据
                if(!empty($id)){
                    $oldRow = Relation::find($id);
                }
                //查询是否跟已有数据重复
                $row = Relation::where(['name'=>$name,'case'=>$case])->first();
                if(!$row){
                    //不重复
                    if(isset($oldRow) && $oldRow){
                        //有旧的记录-修改旧数据
                        $row = $oldRow;
                    }else{
                        //没找到旧的记录-新建
                        $row = new Relation();
                    }
                }else{
                    //重复-如果与旧的id不同旧报错
                    if(isset($oldRow) && $oldRow && $row->id !== $id){
                        $error .= "重复的数据：{$id} - {$word}\n";
                        $currLine++;
                        $countFail++;
                        continue;
                    }
                }
                $row->name = $name;
                $row->case = $case;
                $row->to = $to;
                $row->editor_id = $user['user_uid'];
                $row->save();
            }else{
                break;
            }
            $currLine++;
        } while (true);
        return $this->ok(["success"=>$currLine-2-$countFail,'fail'=>($countFail)],$error);
    }
}
