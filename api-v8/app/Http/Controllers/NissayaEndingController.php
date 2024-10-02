<?php

namespace App\Http\Controllers;

use App\Models\NissayaEnding;
use App\Models\Relation;
use App\Models\DhammaTerm;
use Illuminate\Http\Request;
use App\Http\Resources\NissayaEndingResource;
use App\Http\Api\AuthApi;
use App\Http\Api\ChannelApi;
use Illuminate\Support\Facades\App;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use mustache\mustache;

class NissayaEndingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $table = NissayaEnding::select(['id','ending','lang','relation',
                                        'case','from','count','editor_id',
                                        'created_at','updated_at']);

        if(($request->has('case'))){
            $table->whereIn('case', explode(",",$request->get('case')) );
        }

        if(($request->has('lang'))){
            $table->whereIn('lang', explode(",",$request->get('lang')) );
        }

        if(($request->has('relation'))){
            $table->where('relation', $request->get('relation'));
        }
        if(($request->has('case'))){
            $table->where('case', $request->get('case'));
        }

        if(($request->has('search'))){
            $table->where('ending', 'like', "%".$request->get('search')."%");
        }

        $count = $table->count();

        $table->orderBy($request->get('order','updated_at'),
                        $request->get('dir','desc'));

        $table->skip($request->get("offset",0))
              ->take($request->get('limit',1000));
        $result = $table->get();

        return $this->ok(["rows"=>NissayaEndingResource::collection($result),"count"=>$count]);
    }

    public function vocabulary(Request $request){
        $result = NissayaEnding::select(['ending'])
                              ->where('lang', $request->get('lang') )
                              ->groupBy('ending')
                              ->get();
        return $this->ok(["rows"=>$result,"count"=>count($result)]);
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
            'ending' => 'required',
            'lang' => 'required',
        ]);
        $new = new NissayaEnding;
        $new->ending = $validated['ending'];
        $new->strlen = mb_strlen($validated['ending'],"UTF-8") ;
        $new->lang = $validated['lang'];
        $new->relation = $request->get('relation');
        $new->case = $request->get('case');
        if($request->has('from')){
            $new->from = json_encode($request->get('from'),JSON_UNESCAPED_UNICODE);
        }else{
            $new->from = null;
        }
        $new->editor_id = $user['user_uid'];
        $new->save();
        return $this->ok(new NissayaEndingResource($new));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\NissayaEnding  $nissayaEnding
     * @return \Illuminate\Http\Response
     */
    public function show(NissayaEnding $nissayaEnding)
    {
        //
        return $this->ok(new NissayaEndingResource($nissayaEnding));

    }

    public function nissaya_card(Request $request)
    {
        //
        $cardData = [];
        App::setLocale($request->get('lang'));
        $localTerm = ChannelApi::getSysChannel(
                                "_System_Grammar_Term_".strtolower($request->get('lang'))."_",
                                "_System_Grammar_Term_en_"
                            );
        if(!$localTerm){
            return $this->error('no term channel');
        }
        $termTable = DhammaTerm::where('channal',$localTerm);
        $cardData['ending'] = $request->get('ending');
        $endingTerm = $termTable->where('word',$request->get('ending'))->first();
        if($endingTerm){
            $cardData['ending_tag'] = $endingTerm->tag;
            $cardData['ending_meaning'] = $endingTerm->meaning;
            $cardData['ending_note'] = $endingTerm->note;
        }

        $myEnding = NissayaEnding::where('ending',$request->get('ending'))
                                 ->groupBy('relation')
                                 ->select('relation')->get();
        if(count($myEnding) === 0){
            if(!isset($cardData['ending_note'])){
                $cardData['ending_note'] = "no record\n";
            }
        }

        $relations = Relation::whereIn('name',$myEnding)->get();
        if(count($relations) > 0){
            $cardData['title_case'] = "本词";
            $cardData['title_relation'] = "关系";
            $cardData['title_local_relation'] = "关系";
            $cardData['title_local_link_to'] = "目标词特征";
            $cardData['title_content'] = "含义";
            $cardData['title_local_ending'] = "翻译建议";

            foreach ($relations as $key => $relation) {
                $relationInTerm = DhammaTerm::where('channal',$localTerm)
                                            ->where('word',$relation['name'])
                                            ->first();
                if(empty($relation->from)){
                    $cardData['row'][] = ["relation"=>$relation->name];
                    continue;
                }
                $from = json_decode($relation->from);
                if(isset($from->case)){
                    $cases = $from->case;
                    $localCase  =[];
                    foreach ($cases as $case) {
                        $localCase[] = ['label'=>__("grammar.".$case),
                                        'link'=>config('mint.server.dashboard_base_path').'/term/list/'.$case
                                        ];
                    }
                    # 格位
                    $newLine['case'] = $localCase;
                }
                if(isset($from->spell)){
                    $newLine['spell'] = $from->spell;
                }
                //连接到
                $linkTos = json_decode($relation->to);
                if(count($linkTos)>0){
                    $localTo  =[];
                    foreach ($linkTos as $to) {
                        $localTo[] = ['label'=>__("grammar.".$to),
                                        'link'=>config('mint.server.dashboard_base_path').'/term/list/'.$case
                                        ];
                    }
                    # 格位
                    $newLine['to'] = $localTo;
                }
                //含义 用分类字段的term 数据
                if(isset($relation['category']) && !empty($relation['category'])){
                    $localCategory = DhammaTerm::where('channal',$localTerm)
                                                ->where('word',$relation['category'])
                                                ->first();
                    if($localCategory){
                        $newLine['category_note'] = $localCategory->note;
                    }
                }

                /**
                 * 翻译建议
                 * relation 和 from 都匹配成功
                 * from 为空 只匹配 relation
                 */
                $arrLocalEnding = array();
                $localEndings = NissayaEnding::where('relation',$relation['name'])
                                                  ->where('lang',$request->get('lang'))
                                                  ->get();
                foreach ($localEndings as $localEnding) {
                    if(empty($localEnding->from) || $localEnding->from===$relation->from){
                        $arrLocalEnding[]=$localEnding->ending;
                    }
                }
                $newLine['local_ending'] = implode(';',$arrLocalEnding);

                //本地语言 关系名称
                if($relationInTerm){
                    $newLine['local_relation'] =  $relationInTerm->meaning;
                }
                //关系名称
                $newLine['relation'] =  $relation['name'];
                $newLine['relation_link'] =  config('mint.server.dashboard_base_path').'/term/list/'.$relation['name'];
                $cardData['row'][] = $newLine;
            }
        }


        $m = new \Mustache_Engine(array('entity_flags'=>ENT_QUOTES));
        $tpl = file_get_contents(resource_path("mustache/nissaya_ending_card.tpl"));
        $md = $m->render($tpl,$cardData);
        return $this->ok($md);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\NissayaEnding  $nissayaEnding
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NissayaEnding $nissayaEnding)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //查询是否重复
        /*
        $table = NissayaEnding::where('ending',$request->get('ending'))
                 ->where('lang',$request->get('lang'))
                 ->where('relation',$request->get('relation'));
        $from = json_encode($request->get('from'),JSON_UNESCAPED_UNICODE);
        if(empty($from)){
            $table = $table->whereNull('from');
        }else{
            $json = $request->get('from');
            $table = $table->whereJsonContains('from',['case'=>$json['case']]);
        }
        if($table->exists()){
            return $this->error(__('validation.exists',['name']));
        }
*/
        $nissayaEnding->ending = $request->get('ending');
        $nissayaEnding->strlen = mb_strlen($request->get('ending'),"UTF-8") ;
        $nissayaEnding->lang = $request->get('lang');
        $nissayaEnding->relation = $request->get('relation');
        if($request->has('from') && !empty($request->get('from'))){
            $nissayaEnding->from = json_encode($request->get('from'),JSON_UNESCAPED_UNICODE);
        }else{
            $nissayaEnding->from = null;
        }
        $nissayaEnding->editor_id = $user['user_uid'];
        $nissayaEnding->save();
        return $this->ok(new NissayaEndingResource($nissayaEnding));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\NissayaEnding  $nissayaEnding
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,NissayaEnding $nissayaEnding)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //TODO 判断当前用户是否有权限
        $delete = 0;
        $delete = $nissayaEnding->delete();

        return $this->ok($delete);
    }

    public function export(){
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $activeWorksheet->setCellValue('A1', 'id');
        $activeWorksheet->setCellValue('B1', 'ending');
        $activeWorksheet->setCellValue('C1', 'lang');
        $activeWorksheet->setCellValue('D1', 'relation');

        $nissaya = NissayaEnding::cursor();
        $currLine = 2;
        foreach ($nissaya as $key => $row) {
            # code...
            $activeWorksheet->setCellValue("A{$currLine}", $row->id);
            $activeWorksheet->setCellValue("B{$currLine}", $row->ending);
            $activeWorksheet->setCellValue("C{$currLine}", $row->lang);
            $activeWorksheet->setCellValue("D{$currLine}", $row->relation);
            $activeWorksheet->setCellValue("E{$currLine}", $row->case);
            $currLine++;
        }
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="nissaya-ending.xlsx"');
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
            $ending = $activeWorksheet->getCell("B{$currLine}")->getValue();
            $lang = $activeWorksheet->getCell("C{$currLine}")->getValue();
            $relation = $activeWorksheet->getCell("D{$currLine}")->getValue();
            $case = $activeWorksheet->getCell("E{$currLine}")->getValue();
            if(!empty($ending)){
                //查询是否有冲突数据
                //查询此id是否有旧数据
                if(!empty($id)){
                    $oldRow = NissayaEnding::find($id);
                }
                //查询是否跟已有数据重复
                $row = NissayaEnding::where(['ending'=>$ending,'relation'=>$relation,'case'=>$case])->first();
                if(!$row){
                    //不重复
                    if(isset($oldRow) && $oldRow){
                        //有旧的记录-修改旧数据
                        $row = $oldRow;
                    }else{
                        //没找到旧的记录-新建
                        $row = new NissayaEnding();
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
                $row->ending = $ending;
                $row->strlen = mb_strlen($ending,"UTF-8") ;
                $row->lang = $lang;
                $row->relation = $relation;
                $row->case = $case;
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
