<?php

namespace App\Http\Controllers;

use App\Models\NissayaEnding;
use Illuminate\Http\Request;
use mustache\mustache;
use App\Http\Api\ChannelApi;
use App\Http\Api\MdRender;
use Illuminate\Support\Facades\App;
use App\Models\DhammaTerm;
use App\Models\Relation;

class NissayaCardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  string  $nissayaEnding
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,string $nissayaEnding)
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

        $cardData['ending']['word'] = $nissayaEnding;
        $endingTerm = $termTable->where('word',$nissayaEnding)->first();
        if($endingTerm){
            $cardData['ending']['id'] = $endingTerm->guid;
            $cardData['ending']['tag'] = $endingTerm->tag;
            $cardData['ending']['meaning'] = $endingTerm->meaning;
            $cardData['ending']['note'] = $endingTerm->note;
            if(!empty($endingTerm->note)){
                $mdRender = new MdRender(
                    [
                        'mode'=>'read',
                        'format'=>'react',
                        'lang'=>$endingTerm->lang,
                    ]);
                    $cardData['ending']['html']  = $mdRender->convert($endingTerm->note,[],null);
            }
        }

        $myEnding = NissayaEnding::where('ending',$nissayaEnding)
                                 ->select('relation')->get();
        if(count($myEnding) === 0){
            if(!isset($cardData['ending']['note'])){
                $cardData['ending']['note'] = "no record\n";
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
                $newLine = array();
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
                                        'case'=>$case,
                                        'link'=>config('mint.server.dashboard_base_path').'/term/list/'.$case
                                        ];
                    }
                    # 格位
                    $newLine['from']['case'] = $localCase;
                }
                if(isset($from->spell)){
                    $newLine['from']['spell'] = $from->spell;
                }
                //连接到
                $linkTos = json_decode($relation->to);
                if(isset($linkTos->case) && is_array($linkTos->case) && count($linkTos->case)>0){
                    $localTo  =[];
                    foreach ($linkTos->case as $to) {
                        $localTo[] = [
                            'label'=>__("grammar.".$to),
                            'case'=>$to,
                            'link'=>config('mint.server.dashboard_base_path').'/term/list/'.$to
                            ];
                    }

                    # 格位
                    $newLine['to']['case'] = $localTo;
                }
                if(isset($linkTos->spell)){
                    $newLine['to']['spell'] = $linkTos->spell;
                }
                //含义 用分类字段的term 数据
                if(isset($relation['category']) && !empty($relation['category'])){
                    $newLine['category']['name'] = $relation['category'];
                    $localCategory = DhammaTerm::where('channal',$localTerm)
                                                ->where('word',$relation['category'])
                                                ->first();

                    if($localCategory){
                        $mdRender = new MdRender(
                            [
                                'mode'=>'read',
                                'format'=>'text',
                                'lang'=>$endingTerm->lang,
                            ]);
                        $newLine['category']['note'] = $mdRender->convert($localCategory->note,[],null);
                        $newLine['category']['meaning'] =$localCategory->meaning;
                    }else{
                        $newLine['category']['note'] = $relation['category'];
                        $newLine['category']['meaning'] = $relation['category'];
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

        if($request->get('content_type','markdown') === 'markdown'){
            $m = new \Mustache_Engine(array('entity_flags'=>ENT_QUOTES));
            $tpl = file_get_contents(resource_path("mustache/nissaya_ending_card.tpl"));
            $result = $m->render($tpl,$cardData);
        }else{
            $result = $cardData;
        }

        return $this->ok($result);
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
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\NissayaEnding  $nissayaEnding
     * @return \Illuminate\Http\Response
     */
    public function destroy(NissayaEnding $nissayaEnding)
    {
        //
    }
}
