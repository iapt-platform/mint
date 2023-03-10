<?php


namespace App\Http\Controllers;

use App\Models\SentPr;
use App\Models\Channel;
use App\Models\PaliSentence;
use App\Models\Sentence;
use App\Http\Resources\SentPrResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

require_once __DIR__.'/../../../public/app/ucenter/function.php';

class SentPrController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->get('view')) {
            case 'sent-info':
                $table = SentPr::where('book_id',$request->get('book'))
                                ->where('paragraph',$request->get('para'))
                                ->where('word_start',$request->get('start'))
                                ->where('word_end',$request->get('end'))
                                ->where('channel_uid',$request->get('channel'));
                $all_count = $table->count();
                $chapters = $table->orderBy('created_at','desc')->get();

                break;
        }
        if($chapters){
            return $this->ok(["rows"=>SentPrResource::collection($chapters),"count"=>$all_count]);
        }else{
            return $this->error("no data");
        }
    }

    public function pr_tree(Request $request){
        $output = [];
        $sentences = $request->get("data");
        foreach ($sentences as $key => $sentence) {
            # 先查句子信息
            $sentInfo = Sentence::where('book_id',$sentence['book'])
                                ->where('paragraph',$sentence['paragraph'])
                                ->where('word_start',$sentence['word_start'])
                                ->where('word_end',$sentence['word_end'])
                                ->where('channel_uid',$sentence['channel_id'])
                                ->first();
            $sentPr = SentPr::where('book_id',$sentence['book'])
                            ->where('paragraph',$sentence['paragraph'])
                            ->where('word_start',$sentence['word_start'])
                            ->where('word_end',$sentence['word_end'])
                            ->where('channel_uid',$sentence['channel_id'])
                            ->select('content','editor_uid')
                            ->orderBy('created_at','desc')->get();
            $output[] = [
                'sentence' => [
                    'book' => $sentInfo->book_id,
                    'paragraph' => $sentInfo->paragraph,
                    'word_start' => $sentInfo->word_start,
                    'word_end' => $sentInfo->word_end,
                    'channel_id' => $sentInfo->channel_uid,
                    'content' => $sentInfo->content,
                    'pr_count' => count($sentPr),
                ],
                'pr' => $sentPr,
            ];
        }
        return $this->ok(['rows'=>$output,'count'=>count($output)]);
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
        $user = \App\Http\Api\AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        $user_uid = $user['user_uid'];

        $data = $request->all();


		#查询是否存在
		#同样的内容只能提交一次
		$exists = SentPr::where('book_id',$data['book'])
						->where('paragraph',$data['para'])
						->where('word_start',$data['begin'])
						->where('word_end',$data['end'])
						->where('content',$data['text'])
						->where('channel_uid',$data['channel'])
						->exists();
		if(!$exists){
			#不存在，新建
			$new = new SentPr();
			$new->id = app('snowflake')->id();
			$new->book_id = $data['book'];
			$new->paragraph = $data['para'];
			$new->word_start = $data['begin'];
			$new->word_end = $data['end'];
			$new->channel_uid = $data['channel'];
			$new->editor_uid = $user_uid;
			$new->content = $data['text'];
			$new->language = Channel::where('uid',$data['channel'])->value('lang');
			$new->status = 1;//未处理状态
			$new->strlen = mb_strlen($data['text'],"UTF-8");
			$new->create_time = time()*1000;
			$new->modify_time = time()*1000;
			$new->save();
		}

		$robotMessageOk=false;
		$webHookMessage="";
		if(app()->isLocal()==false)
		{
			/*
			初译：e5bc5c97-a6fb-4ccb-b7df-be6dcfee9c43
			模版：#用户名 就“##该句子巴利前20字符##”提出了这样的修改建议：“##PR内容前20字##”，欢迎大家[点击链接](句子/段落链接)前往查看并讨论。

			问题集：8622ad73-deef-4525-8e8e-ba3f1462724e
			模版：#用户名 就 “##该句子巴利前20字符##”有这样的疑问：“##PR内容前20字##”，欢迎大家[点击链接](句子/段落链接)参与讨论。

			初步答疑：5ab653d7-1ae3-40b0-ae07-c3d530a2a8f8
			模版：#用户名 就“##该句子巴利前20字符##”中的问题做了这样的回复：“##PR内容前20字##”，欢迎大家[点击链接](句子/段落链接)前往查看并讨论。

			机器人地址：https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=25dbd74f-c89c-40e5-8cbc-48b1ef7710b8

			项目范围：
			book65 par：829-1306
			book67 par：759-1152
			*/

			//if(($data['book']==65 && $data['para']>=829 && $data['para']<=1306) || ($data['book']== 67 && $data['para'] >= 759 && $data['para'] <= 1152)){
				$userinfo = new \UserInfo();

				$username = $userinfo->getName($user_uid)['nickname'];
				$palitext = PaliSentence::where('book',$data['book'])
										->where('paragraph',$data['para'])
										->where('word_begin',$data['begin'])
										->where('word_end',$data['end'])
										->value('text');
				$sent_num = "{$data['book']}-{$data['para']}-{$data['begin']}-{$data['end']}";
				$palitext = mb_substr($palitext,0,20,"UTF-8");
				$prtext = mb_substr($data['text'],0,140,"UTF-8");
				$link = "https://www-hk.wikipali.org/app/article/index.php?view=para&book={$data['book']}&par={$data['para']}&begin={$data['begin']}&end={$data['end']}&channel={$data['channel']}&mode=edit";
				Log::info("palitext:{$palitext} prtext = {$prtext} link={$link}");
				switch ($data['channel']) {
					//测试
					//case '3b0cb0aa-ea88-4ce5-b67d-00a3e76220cc':
					//正式
					case 'e5bc5c97-a6fb-4ccb-b7df-be6dcfee9c43':
						$strMessage = "{$username} 就文句`{$palitext}`提出了修改建议：
						>内容摘要：<font color=\"comment\">{$prtext}</font>，\n
						>句子编号：<font color=\"info\">{$sent_num}</font>\n
						欢迎大家[点击链接]({$link}&channel=e5bc5c97-a6fb-4ccb-b7df-be6dcfee9c43,8622ad73-deef-4525-8e8e-ba3f1462724e,5ab653d7-1ae3-40b0-ae07-c3d530a2a8f8)查看并讨论。";
						break;
					case '8622ad73-deef-4525-8e8e-ba3f1462724e':
						$strMessage = "{$username} 就文句`{$palitext}`有疑问：\n
						>内容摘要：<font color=\"comment\">{$prtext}</font>，\n
						>句子编号：<font color=\"info\">{$sent_num}</font>\n
						欢迎大家[点击链接]({$link}&channel=8622ad73-deef-4525-8e8e-ba3f1462724e,5ab653d7-1ae3-40b0-ae07-c3d530a2a8f8)查看并讨论。";
						break;
					case '5ab653d7-1ae3-40b0-ae07-c3d530a2a8f8':
						$strMessage = "{$username} 就文句`{$palitext}`中的疑问有这样的回复：\n
						>内容摘要：<font color=\"comment\">{$prtext}</font>，\n
						>句子编号：<font color=\"info\">{$sent_num}</font>\n
						欢迎大家[点击链接]({$link}&channel=8622ad73-deef-4525-8e8e-ba3f1462724e,5ab653d7-1ae3-40b0-ae07-c3d530a2a8f8)查看并讨论。";
						break;
					default:
						$strMessage = "";
						break;
				}
				$url = "https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=25dbd74f-c89c-40e5-8cbc-48b1ef7710b8";
				$param = [
						"msgtype"=>"markdown",
						"markdown"=> [
							"content"=> $strMessage,
						],
					];
				Log::info("message:{$strMessage}");
				if(!empty($strMessage)){
					$response = Http::post($url, $param);
					if($response->successful()){
						$robotMessageOk = true;
						$webHookMessage = "消息发送成功";
					}else{
						$webHookMessage = "消息发送失败";
						$robotMessageOk = false;
					}
				}else{
					$webHookMessage = "channel不符";
					$robotMessageOk = false;
				}
			//}else{
			//	$webHookMessage = "不在段落范围内";
			//}
		}

		#同时返回此句子pr数量
		$info['book_id'] = $data['book'];
		$info['paragraph'] = $data['para'];
		$info['word_start'] = $data['begin'];
		$info['word_end'] = $data['end'];
		$info['channel_uid'] = $data['channel'];
		$count = SentPr::where('book_id' , $data['book'])
						->where('paragraph' , $data['para'])
						->where('word_start' , $data['begin'])
						->where('word_end' , $data['end'])
						->where('channel_uid' , $data['channel'])
						->count();
		Log::info("count:{$count} webhook-ok={$robotMessageOk}");
		return $this->ok(["new"=>$info,"count"=>$count,"webhook"=>["message"=>$webHookMessage,"ok"=>$robotMessageOk]]);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SentPr  $sentPr
     * @return \Illuminate\Http\Response
     */
    public function show(SentPr $sentPr)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SentPr  $sentPr
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SentPr $sentPr)
    {
        //
		if(!isset($_COOKIE['user_uid'])){
            return $this->error('not login');
        }else{
			$user_uid = $_COOKIE['user_uid'];
		}
		$sentPr = SentPr::where('id',$request->get('id'));
		if($sentPr->value('editor_uid')==$user_uid){
			$update = $sentPr->update([
				"content"=>$request->get('text'),
				"modify_time"=>time()*1000,
			]);
			if($update >= 0){
				$data = SentPr::where('id',$request->get('id'))->first();
				$data->id = sprintf("%d",$data->id);
				return $this->ok($data);
			}else{
				return $this->error('没有更新');
			}

		}else{
			return $this->error('not power');
		}

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
		Log::info("user_uid=" .$_COOKIE['user_uid']);
		$old = SentPr::where('id', $id)->first();
		$result = SentPr::where('id', $id)
							->where('editor_uid', $_COOKIE["user_uid"])
							->delete();
		Log::info("delete=" .$result);
		if($result>0){
					#同时返回此句子pr数量
		$count = SentPr::where('book_id' , $old->book_id)
						->where('paragraph' , $old->paragraph)
						->where('word_start' , $old->word_start)
						->where('word_end' , $old->word_end)
						->where('channel_uid' , $old->channel_uid)
						->count();
			return $this->ok($count);
		}else{
			return $this->error('not power');
		}
    }
}
