<?php

namespace App\Http\Controllers;

use App\Models\DhammaTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use App\Http\Api\AuthApi;
use App\Http\Api\StudioApi;
use App\Http\Api\ChannelApi;
use App\Http\Api\ShareApi;
use App\Tools\Tools;

class TermExportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
//TODO 判断是否有导出权限
        switch ($request->get("view")) {
            case 'channel':
                # code...
                $rows = DhammaTerm::where('channal',$request->get("id"))->cursor();
                break;
            case 'studio':
                # code...
                $studioId = StudioApi::getIdByName($request->get("name"));
                $rows = DhammaTerm::where('owner',$studioId)->cursor();
                break;
            default:
                $this->error('no view');
                break;
        }

        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $activeWorksheet->setCellValue('A1', 'id');
        $activeWorksheet->setCellValue('B1', 'word');
        $activeWorksheet->setCellValue('C1', 'meaning');
        $activeWorksheet->setCellValue('D1', 'other_meaning');
        $activeWorksheet->setCellValue('E1', 'note');
        $activeWorksheet->setCellValue('F1', 'tag');
        $activeWorksheet->setCellValue('G1', 'language');
        $activeWorksheet->setCellValue('H1', 'channel_id');

        $currLine = 2;
        foreach ($rows as $key => $row) {
            # code...
            $activeWorksheet->setCellValue("A{$currLine}", $row->guid);
            $activeWorksheet->setCellValue("B{$currLine}", $row->word);
            $activeWorksheet->setCellValue("C{$currLine}", $row->meaning);
            $activeWorksheet->setCellValue("D{$currLine}", $row->other_meaning);
            $activeWorksheet->setCellValue("E{$currLine}", $row->note);
            $activeWorksheet->setCellValue("F{$currLine}", $row->tag);
            $activeWorksheet->setCellValue("G{$currLine}", $row->language);
            $activeWorksheet->setCellValue("H{$currLine}", $row->channal);
            $currLine++;
        }
        $writer = new Xlsx($spreadsheet);
        $fId = Str::uuid();
        $filename = storage_path("app/tmp/{$fId}");
        $writer->save($filename);
        $key = "download/tmp/{$fId}";
        Redis::set($key,file_get_contents($filename));
        Redis::expire($key,300);
        unlink($filename);
        return $this->ok(['uuid'=>$fId,'filename'=>"term.xlsx",'type'=>"application/vnd.ms-excel"]);
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
     * @param  string  $downloadId
     * @return \Illuminate\Http\Response
     */
    public function show(string $downloadId)
    {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="term.xlsx"');
        $content = Redis::get("download/tmp/{$downloadId}");
        file_put_contents("php://output",$content);
    }

    public function import(Request $request){
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'),401,401);
        }
        /**
         * 判断是否有权限
         */
        switch ($request->get('view')) {
            case 'channel':
                # 向channel里面导入，忽略源数据的channel id 和 owner 都设置为这个channel 的
                $channel = ChannelApi::getById($request->get('id'));
                $owner_id = $channel['studio_id'];
                if($owner_id !== $user["user_uid"]){
                    //判断是否为协作
                    $power = ShareApi::getResPower($user["user_uid"],$request->get('id'));
                    if($power<20){
                        return $this->error(__('auth.failed'),403,403);
                    }
                }
                $language = $channel['lang'];
                break;
            case 'studio':
                # 向 studio 里面导入，忽略源数据的 owner 但是要检测 channel id 是否有权限
                $owner_id = StudioApi::getIdByName($request->get('name'));
                if(!$owner_id){
                    return $this->error('no studio name',403,403);
                }

                break;
        }

        $message = "";
        $filename = $request->get('filename');
        if(Storage::missing($filename)){
            return $this->error('no file '.$filename);
        }
        $contents = Storage::get($filename);
        $fId = Str::uuid();
        $tmpFile = storage_path("app/tmp/{$fId}.xlsx");
        $ok = file_put_contents($tmpFile,$contents);
        if($ok===false){
            return $this->error('create tmp file fail '.$tmpFile,500,500);
        }
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($tmpFile);
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $currLine = 2;
        $countFail = 0;

        do {
            # code...
            $id = $activeWorksheet->getCell("A{$currLine}")->getValue();
            $word = $activeWorksheet->getCell("B{$currLine}")->getValue();
            $meaning = $activeWorksheet->getCell("C{$currLine}")->getValue();
            $other_meaning = $activeWorksheet->getCell("D{$currLine}")->getValue();
            $note = $activeWorksheet->getCell("E{$currLine}")->getValue();
            $tag = $activeWorksheet->getCell("F{$currLine}")->getValue();
            $language = $activeWorksheet->getCell("G{$currLine}")->getValue();
            $channel_id = $activeWorksheet->getCell("H{$currLine}")->getValue();
            $query = ['word'=>$word,'tag'=>$tag];
            $channelId = null;
            switch ($request->get('view')) {
                case 'channel':
                    # 向channel里面导入，忽略源数据的channel id 和 owner 都设置为这个channel 的
                    $query['channal'] = $request->get('id');
                    $channelId = $request->get('id');
                    break;
                case 'studio':
                    # 向 studio 里面导入，忽略源数据的owner 但是要检测 channel id 是否有权限
                    $query['owner'] = $owner_id;
                    if(!empty($channel_id)){

                        //有channel 数据，查看是否在studio中
                        $channel = ChannelApi::getById($channel_id);
                        if($channel === false){
                            $message .= "没有查到版本信息：{$channel_id} - {$word}\n";
                            $currLine++;
                            $countFail++;
                            continue 2;
                        }
                        if($owner_id != $channel['studio_id']){
                            $message .= "版本不在studio中：{$channel_id} - {$word}\n";
                            $currLine++;
                            $countFail++;
                            continue 2;
                        }
                        $query['channal'] = $channel_id;
                        $channelId = $channel_id;
                    }
                    # code...
                    break;
            }

            if(empty($id) && empty($word)){
                break;
            }

            //查询此id是否有旧数据
            if(!empty($id)){
                $oldRow = DhammaTerm::find($id);
                //TODO 有 id 无 word 删除数据
                if(empty($word)){
                    //查看权限
                    if($oldRow->owner !== $user['user_uid']){
                        if(!empty($oldRow->channal)){
                            //看是否为协作
                            $power = ShareApi::getResPower($user['user_uid'],$oldRow->channal);
                            if($power < 20){
                                $message .= "无删除权限：{$id} - {$word}\n";
                                $currLine++;
                                $countFail++;
                                continue;
                            }
                        }else{
                            $message .= "无删除权限：{$id} - {$word}\n";
                            $currLine++;
                            $countFail++;
                            continue;
                        }
                    }
                    //删除
                    $oldRow->delete();
                    $currLine++;
                    continue;
                }
            }else{
                $oldRow = null;
            }
            //查询是否跟已有数据重复
            $row = DhammaTerm::where($query)->first();
            if(!$row){
                //不重复
                if(isset($oldRow) && $oldRow){
                    //找到旧的记录-修改旧数据
                    $row = $oldRow;
                }else{
                    //没找到旧的记录-新建
                    $row = new DhammaTerm();
                    $row->id = app('snowflake')->id();
                    $row->guid = Str::uuid();
                    $row->word = $word;
                    $row->create_time = time()*1000;
                }
            }else{
                //重复-如果与旧的id不同,报错
                if(isset($oldRow) && $oldRow && $row->guid !== $id){
                    $message .= "重复的数据：{$id} - {$word}\n";
                    $currLine++;
                    $countFail++;
                    continue;
                }
            }
            $row->word = $word;
            $row->word_en = Tools::getWordEn($word);
            $row->meaning = $meaning;
            $row->other_meaning = $other_meaning;
            $row->note = $note;
            $row->tag = $tag;
            $row->language = $language;
            $row->channal = $channelId;
            $row->editor_id = $user['user_id'];
            $row->owner = $owner_id;
            $row->modify_time = time()*1000;
            $row->save();

            $currLine++;
        } while (true);
        unlink($tmpFile);
        return $this->ok(["success"=>$currLine-2-$countFail,'fail'=>($countFail)],$message);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DhammaTerm  $dhammaTerm
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DhammaTerm $dhammaTerm)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DhammaTerm  $dhammaTerm
     * @return \Illuminate\Http\Response
     */
    public function destroy(DhammaTerm $dhammaTerm)
    {
        //
    }
}
