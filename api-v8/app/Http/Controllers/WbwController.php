<?php

namespace App\Http\Controllers;

use App\Models\Wbw;
use App\Models\WbwBlock;
use App\Models\Channel;
use App\Models\PaliSentence;
use App\Models\Sentence;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Tools\Tools;
use App\Http\Api\AuthApi;
use App\Http\Api\ShareApi;
use App\Http\Api\ChannelApi;
use App\Http\Api\Mq;

class WbwController extends Controller
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
     * 新建多个
     * 如果存在，修改
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        //鉴权
        $user = AuthApi::current($request);
        if(!$user ){
            //未登录用户
            return $this->error(__('auth.failed'),[],401);
        }
        $channel = Channel::where('uid',$request->get('channel_id'))->first();
        if(!$channel){
            return $this->error(__('auth.failed'));
        }
        if($channel->owner_uid !== $user["user_uid"]){
            //判断是否为协作
            $power = ShareApi::getResPower($user["user_uid"],$channel->uid);
            if($power < 20){
                return $this->error(__('auth.failed'),[],403);
            }
        }
        //查看WbwBlock是否已经建立
        $wbwBlockId = WbwBlock::where('book_id',$request->get('book'))
                            ->where('paragraph',$request->get('para'))
                            ->where('channel_uid',$request->get('channel_id'))
                            ->value('uid');
        if(!Str::isUuid($wbwBlockId)){
            $wbwBlock = new WbwBlock();
            $wbwBlockId = Str::uuid();
            $wbwBlock->id = app('snowflake')->id();
			$wbwBlock->uid = $wbwBlockId;
            $wbwBlock->creator_uid = $user["user_uid"];
            $wbwBlock->editor_id = $user["user_id"];
            $wbwBlock->book_id = $request->get('book');
            $wbwBlock->paragraph = $request->get('para');
            $wbwBlock->channel_uid = $request->get('channel_id');
            $wbwBlock->lang = $channel->lang;
            $wbwBlock->status = $channel->status;
            $wbwBlock->create_time = time()*1000;
            $wbwBlock->modify_time = time()*1000;
            $wbwBlock->save();
        }
        $wbw = Wbw::where('block_uid',$wbwBlockId)
                  ->where('wid',$request->get('sn'))
                  ->first();
        $sent = PaliSentence::where('book',$request->get('book'))
                                ->where('paragraph',$request->get('para'))
                                ->where('word_begin',"<=",$request->get('sn'))
                                ->where('word_end',">=",$request->get('sn'))
                                ->first();
        if(!$wbw){
            //建立一个句子的逐词解析数据
            //找到句子

            $channelId = ChannelApi::getSysChannel('_System_Wbw_VRI_');
            $wbwContent = Sentence::where('book_id',$sent->book)
							->where('paragraph',$sent->paragraph)
							->where('word_start',$sent->word_begin)
							->where('word_end',$sent->word_end)
							->where('channel_uid',$channelId)
							->value('content');
            $words = json_decode($wbwContent);
            foreach ($words as $word) {
                # code...
                $xmlObj = simplexml_load_string("<word></word>");
                $xmlObj->addChild('id',"{$sent->book}-{$sent->paragraph}-{$word->sn[0]}");
                $xmlObj->addChild('pali',$word->word->value)->addAttribute('status',0);
                $xmlObj->addChild('real',$word->real->value)->addAttribute('status',0);
                $xmlObj->addChild('type',$word->type->value)->addAttribute('status',0);
                $xmlObj->addChild('gramma',$word->grammar->value)->addAttribute('status',0);
                $xmlObj->addChild('case',$word->case->value)->addAttribute('status',0);
                $xmlObj->addChild('style',$word->style->value)->addAttribute('status',0);
                $xmlObj->addChild('org',$word->factors->value)->addAttribute('status',0);
                $xmlObj->addChild('om',$word->factorMeaning->value)->addAttribute('status',0);
                $xmlObj->addChild('status',1);
                $xml = $xmlObj->asXml();
                $xml = str_replace('<?xml version="1.0"?>','',$xml);

                $newWbw = new Wbw();
                $newWbw->id = app('snowflake')->id();
                $newWbw->uid = Str::uuid();
                $newWbw->creator_uid = $channel->owner_uid;
                $newWbw->editor_id = $user["user_id"];
                $newWbw->book_id = $request->get('book');
                $newWbw->paragraph = $request->get('para');
                $newWbw->wid = $word->sn[0];
                $newWbw->block_uid = $wbwBlockId;
                $newWbw->data = $xml;
                $newWbw->word = $word->real->value;
                $newWbw->status = 0;
                $newWbw->create_time = time()*1000;
                $newWbw->modify_time = time()*1000;
                $newWbw->save();
                if($word->sn[0] === $request->get('sn')){
                    $wbw = $newWbw;
                }
            }
        }

        $count=0;
        $wbwId = array();
        foreach ($request->get('data') as $row) {
            $wbw = Wbw::where('block_uid',$wbwBlockId)
                        ->where('wid',$row['sn'])
                        ->first();
            if($wbw){
                $wbwData = "";
                foreach ($row['words'] as $word) {
                    $xml = Tools::JsonToXml($word);
                    $xml = str_replace('<?xml version="1.0"?>','',$xml);
                    $wbwData .= $xml;
                }
                $wbw->data = $wbwData;
                $wbw->status = 5;
                $wbw->save();
                $wbwId[] = $wbw->id;
                $count++;
            }
        }
        //获取整个句子数据
        $corpus = new CorpusController;
        $wbwString = $corpus->getWbw($request->get('book'),
                            $request->get('para'),
                            $sent->word_begin,
                            $sent->word_end,
                            $request->get('channel_id'));
        if($wbwString){
            $wbwSentence = json_decode($wbwString);
        }else{
            $wbwSentence = [];
        }


        if(count($wbwId)>0){
            Mq::publish('wbw-analyses',$wbwId);
        }

        return $this->ok(['rows'=>$wbwSentence,"count"=>$count]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Wbw  $wbw
     * @return \Illuminate\Http\Response
     */
    public function show(Wbw $wbw)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Wbw  $wbw
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Wbw $wbw)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Wbw  $wbw
     * @return \Illuminate\Http\Response
     */
    public function destroy(Wbw $wbw)
    {
        //
    }
}
