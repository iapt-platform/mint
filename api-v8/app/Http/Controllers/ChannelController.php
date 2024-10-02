<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

use App\Models\Channel;
use App\Models\Sentence;
use App\Models\DhammaTerm;
use App\Models\WbwBlock;
use App\Models\PaliSentence;
use App\Models\CustomBook;

use App\Http\Controllers\AuthController;
use App\Http\Resources\ChannelResource;

use App\Http\Api\AuthApi;
use App\Http\Api\StudioApi;
use App\Http\Api\ShareApi;
use App\Http\Api\PaliTextApi;
use App\Http\Api\ChannelApi;


class ChannelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
		$result=false;
		$indexCol = ['uid','name','summary',
                    'type','owner_uid','lang',
                    'status','is_system','updated_at','created_at'];
		switch ($request->get('view')) {
            case 'public':
                $table = Channel::select($indexCol)
                            ->where('status',30);

                break;
            case 'studio':
				# 获取studio内所有channel
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'));
                }
                //判断当前用户是否有指定的studio的权限
                $studioId = StudioApi::getIdByName($request->get('name'));
                if($user['user_uid'] !== $studioId){
                    return $this->error(__('auth.failed'));
                }

                $table = Channel::select($indexCol);
                if($request->get('view2','my')==='my'){
                    $table = $table->where('owner_uid', $studioId);
                }else{
                    //协作
                    $resList = ShareApi::getResList($studioId,2);
                    $resId=[];
                    foreach ($resList as $res) {
                        $resId[] = $res['res_id'];
                    }
                    $table = $table->whereIn('uid', $resId);
                    if($request->get('collaborator','all') !== 'all'){
                        $table = $table->where('owner_uid', $request->get('collaborator'));
                    }else{
                        $table = $table->where('owner_uid','<>', $studioId);
                    }
                }
				break;
            case 'studio-all':
                /**
                 * studio 的和协作的
                 */
                #获取user所有有权限的channel列表
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'));
                }
                //判断当前用户是否有指定的studio的权限
                if($user['user_uid'] !== StudioApi::getIdByName($request->get('name'))){
                    return $this->error(__('auth.failed'));
                }
                $channelById = [];
                $channelId = [];
                //获取共享channel
                $allSharedChannels = ShareApi::getResList($user['user_uid'],2);
                foreach ($allSharedChannels as $key => $value) {
                    # code...
                    $channelId[] = $value['res_id'];
                    $channelById[$value['res_id']] = $value;
                }
                $table = Channel::select($indexCol)
                            ->whereIn('uid', $channelId)
                            ->orWhere('owner_uid',$user['user_uid']);
                break;
            case 'user-edit':
                /**
                 * 某用户有编辑权限的
                 */
                #获取user所有有权限的channel列表
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'));
                }
                $channelById = [];
                $channelId = [];
                //获取共享channel
                $allSharedChannels = ShareApi::getResList($user['user_uid'],2);
                foreach ($allSharedChannels as $key => $value) {
                    # code...
                    if($value['power']>=20){
                        $channelId[] = $value['res_id'];
                        $channelById[$value['res_id']] = $value;
                    }
                }
                $table = Channel::select($indexCol)
                            ->whereIn('uid', $channelId)
                            ->orWhere('owner_uid',$user['user_uid']);
                break;
            case 'user-in-chapter':
                #获取user 在某章节 所有有权限的channel列表
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'));
                }
                $channelById = [];
                $channelId = [];
                //获取共享channel
                $allSharedChannels = ShareApi::getResList($user['user_uid'],2);
                foreach ($allSharedChannels as $key => $value) {
                    # code...
                    $channelId[] = $value['res_id'];
                    $channelById[$value['res_id']] = $value;
                }
                //获取全网公开channel
                $chapter = PaliTextApi::getChapterStartEnd($request->get('book'),$request->get('para'));
                $publicChannelsWithContent = Sentence::where('book_id',$request->get('book'))
                                            ->whereBetween('paragraph',$chapter)
                                            ->where('strlen','>',0)
                                            ->where('status',30)
                                            ->groupBy('channel_uid')
                                            ->select('channel_uid')
                                            ->get();
                foreach ($publicChannelsWithContent as $key => $value) {
                    # code...
                    $value['res_id']=$value->channel_uid;
                    $value['power'] = 10;
                    $value['type'] = 2;
                    if(!isset($channelById[$value['res_id']])){
                        $channelId[] = $value['res_id'];
                        $channelById[$value['res_id']] = $value;
                    }
                }
                $table = Channel::select($indexCol)
                        ->whereIn('uid', $channelId)
                        ->orWhere('owner_uid',$user['user_uid']);
                break;
            case 'system':
                $table = Channel::select($indexCol)
                            ->where('owner_uid',config("mint.admin.root_uuid"));
                break;
        }
        //处理搜索
        if(!empty($request->get("search"))){
            $table = $table->where('name', 'like', "%".$request->get("search")."%");
        }
        if($request->has("type")){
            $table = $table->where('type', $request->get("type"));
        }
        if($request->has("updated_at")){
            $table = $table->where('updated_at','>', $request->get("updated_at"));
        }
        if($request->has("created_at")){
            $table = $table->where('created_at','>', $request->get("created_at"));
        }
        //获取记录总条数
        $count = $table->count();
        //处理排序
        $table = $table->orderBy($request->get("order",'created_at'),
                                 $request->get("dir",'desc'));
        //处理分页
        $table = $table->skip($request->get("offset",0))
                       ->take($request->get("limit",200));
        //获取数据
        $result = $table->get();
//TODO 将下面代码转移到resource
        if($result){
            if($request->has('progress')){
                //获取进度
                //获取单句长度
                $sentLen = PaliSentence::where('book',$request->get('book'))
                                        ->whereBetween('paragraph',$chapter)
                                        ->orderBy('word_begin')
                                        ->select(['book','paragraph','word_begin','word_end','length'])
                                        ->get();
            }
            foreach ($result as $key => $value) {
                if($request->has('progress')){
                    //获取进度
                    $finalTable = Sentence::where('book_id',$request->get('book'))
                                        ->whereBetween('paragraph',$chapter)
                                        ->where('channel_uid',$value->uid)
                                        ->where('strlen','>',0)
                                        ->select(['strlen','book_id','paragraph','word_start','word_end']);
                    if($finalTable->count()>0){
                        $finished = $finalTable->get();
                        $final=[];
                        foreach ($sentLen as  $sent) {
                            # code...
                            $first = Arr::first($finished, function ($value, $key) use($sent) {
                                return ($value->book_id==$sent->book &&
                                        $value->paragraph==$sent->paragraph &&
                                        $value->word_start==$sent->word_begin &&
                                        $value->word_end==$sent->word_end);
                            });
                            $final[] = [$sent->length,$first?true:false];
                        }
                        $value['final'] = $final;
                    }
                }
                //角色
                if(isset($user['user_uid'])){
                    if($value->owner_uid===$user['user_uid']){
                        $value['role'] = 'owner';
                    }else{
                        if(isset($channelById[$value->uid])){
                            switch ($channelById[$value->uid]['power']) {
                                case 10:
                                    # code...
                                    $value['role'] = 'member';
                                    break;
                                case 20:
                                    $value['role'] = 'editor';
                                    break;
                                case 30:
                                    $value['role'] = 'owner';
                                    break;
                                default:
                                    # code...
                                    $value['role'] = $channelById[$value->uid]['power'];
                                    break;
                            }
                        }
                    }
                }
                # 获取studio信息
                $value->studio = StudioApi::getById($value->owner_uid);
            }
			return $this->ok(["rows"=>$result,"count"=>$count]);
		}else{
			return $this->error("没有查询到数据");
		}

    }

    /**
     * 获取我的，和协作channel数量
     *
     * @return \Illuminate\Http\Response
     */
    public function showMyNumber(Request $request){
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //判断当前用户是否有指定的studio的权限
        $studioId = StudioApi::getIdByName($request->get('studio'));
        if($user['user_uid'] !== $studioId){
            return $this->error(__('auth.failed'));
        }
        //我的
        $my = Channel::where('owner_uid', $studioId)->count();
        //协作
        $resList = ShareApi::getResList($studioId,2);
        $resId=[];
        foreach ($resList as $res) {
            $resId[] = $res['res_id'];
        }
        $collaboration = Channel::whereIn('uid', $resId)->where('owner_uid','<>', $studioId)->count();

        return $this->ok(['my'=>$my,'collaboration'=>$collaboration]);
    }
    /**
     * 获取章节的进度
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function progress(Request $request){
		$indexCol = ['uid','name','summary','type','owner_uid','lang','status','updated_at','created_at'];

        $sent = $request->get('sentence') ;
        $query = [];
        $queryWithChannel = [];
        $sentContainer = [];
        $sentLenContainer = [];

        $paliChannel = ChannelApi::getSysChannel('_System_Pali_VRI_');
        $customBookChannel = array();

        foreach ($sent as $value) {
            $ids = explode('-',$value);
            $idWithChannel = $ids;
            if(count($ids)===4){
                if($ids[0] < 1000){
                    $idWithChannel[] = $paliChannel;
                }else{
                    if(!isset($customBookChannel[$ids[0]])){
                        $cbChannel = CustomBook::where('book_id',$ids[0])->value('channel_id');
                        if($cbChannel){
                            $customBookChannel[$ids[0]] = $cbChannel;
                        }else{
                            $customBookChannel[$ids[0]] = $paliChannel;
                        }
                    }
                    $idWithChannel[] = $customBookChannel[$ids[0]];
                }
                $sentContainer[$value] = false;
                $query[] = $ids;
                $queryWithChannel[] = $idWithChannel;
            }
        }
        //获取单句长度
        if(count($query)>0){
            $table = Sentence::whereIns(['book_id','paragraph','word_start','word_end','channel_uid'],$queryWithChannel)
                                    ->select(['book_id','paragraph','word_start','word_end','strlen']);
            $sentLen = $table->get();

            foreach ($sentLen as $value) {
                $strlen = $value->strlen;
                if(empty($strlen)){
                    $strlen = 0;
                }
                $sentId = "{$value->book_id}-{$value->paragraph}-{$value->word_start}-{$value->word_end}";
                $sentLenContainer[$sentId] = $strlen;
            }
        }

        $channelById = [];
        $channelId = [];

        //获取全网公开的有译文的channel
        if($request->get('owner')==='all' || $request->get('owner')==='public'){
            if(count($query)>0){
                $publicChannelsWithContent = Sentence::whereIns(['book_id','paragraph','word_start','word_end'],$query)
                                            ->where('strlen','>',0)
                                            ->where('status',30)
                                            ->groupBy('channel_uid')
                                            ->select('channel_uid')
                                            ->get();
                foreach ($publicChannelsWithContent as $key => $value) {
                    # code...
                    $value['res_id']=$value->channel_uid;
                    $value['power'] = 10;
                    $value['type'] = 2;
                    if(!isset($channelById[$value['res_id']])){
                        $channelId[] = $value['res_id'];
                        $channelById[$value['res_id']] = $value;
                    }
                }
            }
        }

        #获取 user 在某章节 所有有权限的 channel 列表
        $user = AuthApi::current($request);
        if($user !== false){
            //我自己的
            if($request->get('owner')==='all' || $request->get('owner')==='my'){
                $my = Channel::select($indexCol)->where('owner_uid', $user['user_uid'])->get();
                foreach ($my as $key => $value) {
                    $channelId[] = $value->uid;
                    $channelById[$value->uid] = ['res_id'=>$value->uid,
                                                'power'=>30,
                                                'type'=>2,
                                                ];
                }
            }

            //获取共享channel
            if($request->get('owner')==='all' || $request->get('owner')==='collaborator'){
                $allSharedChannels = ShareApi::getResList($user['user_uid'],2);
                foreach ($allSharedChannels as $key => $value) {
                    # code...
                    if(!in_array($value['res_id'],$channelId)){
                        $channelId[] = $value['res_id'];
                        $channelById[$value['res_id']] = $value;
                    }
                }
            }
        }

        //所有有这些句子译文的channel
        if(count($query) > 0){
            $allChannels = Sentence::whereIns(['book_id','paragraph','word_start','word_end'],$query)
                                        ->where('strlen','>',0)
                                        ->groupBy('channel_uid')
                                        ->select('channel_uid')
                                        ->get();
        }

        //所有需要查询的channel
        $table = Channel::select(['uid','name','summary','type','owner_uid','lang','status','updated_at','created_at'])
                        ->whereIn('uid', $channelId);
        if($user !== false){
            $table->orWhere('owner_uid',$user['user_uid']);
        }
        $result = $table->get();

        foreach ($result as $key => $value) {
            //角色
            if($user!==false && $value->owner_uid===$user['user_uid']){
                $value['role'] = 'owner';
            }else{
                if(isset($channelById[$value->uid])){
                    switch ($channelById[$value->uid]['power']) {
                        case 10:
                            # code...
                            $value['role'] = 'member';
                            break;
                        case 20:
                            $value['role'] = 'editor';
                            break;
                        case 30:
                            $value['role'] = 'owner';
                            break;
                        default:
                            # code...
                            $value['role'] = $channelById[$value->uid]['power'];
                            break;
                    }
                }
            }
            # 获取studio信息
            $result[$key]["studio"] = \App\Http\Api\StudioApi::getById($value->owner_uid);

            //获取进度
            if(count($query) > 0){
                $currChannelId = $value->uid;
                $hasContent = Arr::first($allChannels, function ($value, $key) use($currChannelId) {
                        return ($value->channel_uid===$currChannelId);
                    });
                if($hasContent && count($query)>0){
                    $finalTable = Sentence::whereIns(['book_id','paragraph','word_start','word_end'],$query)
                                            ->where('channel_uid',$currChannelId)
                                            ->where('strlen','>',0)
                                            ->select(['strlen','book_id','paragraph','word_start','word_end','created_at','updated_at']);
                    $created_at = time();
                    $edit_at = 0;
                    if($finalTable->count()>0){
                        $finished = $finalTable->get();
                        $currChannel = [];
                        foreach ($finished as $rowFinish) {
                            $createTime = strtotime($rowFinish->created_at);
                            $updateTime = strtotime($rowFinish->updated_at);
                            if($createTime < $created_at){
                                $created_at = $createTime;
                            }
                            if($updateTime > $edit_at){
                                $edit_at = $updateTime;
                            }
                            $currChannel["{$rowFinish->book_id}-{$rowFinish->paragraph}-{$rowFinish->word_start}-{$rowFinish->word_end}"] = 1;
                        }
                        $final=[];
                        foreach ($sentContainer as $sentId=>$rowSent) {
                            # code...
                            if(isset($currChannel[$sentId])){
                                $final[] = [$sentLenContainer[$sentId],true];
                            }else{
                                $final[] = [$sentLenContainer[$sentId],false];
                            }
                        }
                        $result[$key]['final'] = $final;
                        $result[$key]['content_created_at'] = date('Y-m-d H:i:s',$created_at);
                        $result[$key]['content_updated_at'] = date('Y-m-d H:i:s',$edit_at);
                    }
                }
            }
        }
        return $this->ok(["rows"=>$result,count($result)]);

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
            return $this->error(__('auth.failed'),401,401);
        }
        //判断当前用户是否有指定的studio的权限
        $studioId = StudioApi::getIdByName($request->get('studio'));
        if($user['user_uid'] !== $studioId){
            return $this->error(__('auth.failed'),403,403);
        }
        $studio = StudioApi::getById($studioId);
        //查询是否重复
        if(Channel::where('name',$request->get('name'))
                  ->where('owner_uid',$user['user_uid'])
                  ->exists()){
            return $this->error(__('validation.exists',['name']),200,200);
        }

        $channel = new Channel;
        $channel->id = app('snowflake')->id();
        $channel->name = $request->get('name');
        $channel->owner_uid = $user['user_uid'];
        $channel->type = $request->get('type');
        $channel->lang = $request->get('lang');
        $channel->editor_id = $user['user_id'];
        if(isset($studio['roles'])){
            if(in_array('basic',$studio['roles'])){
                $channel->status = 5;
            }
        }
        $channel->create_time = time()*1000;
        $channel->modify_time = time()*1000;
        $channel->save();
        return $this->ok($channel);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $indexCol = ['uid','name','summary','type','owner_uid','lang','is_system','status','updated_at','created_at'];
		$channel = Channel::where("uid",$id)->select($indexCol)->first();
        $studio = StudioApi::getById($channel->owner_uid);
        $channel->studio = $studio;
        $channel->owner_info = ['nickname'=>$studio['nickName'],'username'=>$studio['realName']];
		return $this->ok($channel);
    }

        /**
     * Display the specified resource.
     *
     * @param  string  $name
     * @return \Illuminate\Http\Response
     */
    public function showByName(string $name)
    {
        //
        $indexCol = ['uid','name','summary','type','owner_uid','lang','is_system','status','updated_at','created_at'];
		$channel = Channel::where("name",$name)->select($indexCol)->first();
        if($channel){
            return $this->ok(new ChannelResource($channel));
        }else{
            return $this->error('no channel');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Channel  $channel
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Channel $channel)
    {
        //鉴权
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'),401,401);
        }
        if($channel->is_system){
            return $this->error('system channel',403,403);
        }
        if($channel->owner_uid !== $user["user_uid"]){
            //判断是否为协作
            $power = ShareApi::getResPower($user["user_uid"],$request->get('id'));
            if($power < 30){
                return $this->error(__('auth.failed'),403,403);
            }
        }
        $channel->name = $request->get('name');
        $channel->type = $request->get('type');
        $channel->summary = $request->get('summary');
        $channel->lang = $request->get('lang');
        $channel->status = $request->get('status');
        $channel->save();
        return $this->ok($channel);
    }
    /**
     * patch the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Channel  $channel
     * @return \Illuminate\Http\Response
     */
    public function patch(Request $request, Channel $channel)
    {
        //鉴权
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'),[],401);
        }
        if($channel->is_system){
            return $this->error('system channel',403,403);
        }
        if($channel->owner_uid !== $user["user_uid"]){
            //判断是否为协作
            $power = ShareApi::getResPower($user["user_uid"],$request->get('id'));
            if($power < 30){
                return $this->error(__('auth.failed'),[],403);
            }
        }
        if($request->has('name')){$channel->name = $request->get('name');}
        if($request->has('type')){$channel->type = $request->get('type');}
        if($request->has('summary')){$channel->summary = $request->get('summary');}
        if($request->has('lang')){$channel->lang = $request->get('lang');}
        if($request->has('status')){$channel->status = $request->get('status');}
        if($request->has('config')){$channel->status = $request->get('config');}
        $channel->save();
        return $this->ok($channel);
    }
    /**
     * Remove the specified resource from storage.
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Channel  $channel
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,Channel $channel)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //判断当前用户是否有指定的studio的权限
        if($user['user_uid'] !== $channel->owner_uid){
            return $this->error(__('auth.failed'));
        }
        //查询其他资源
        if(Sentence::where("channel_uid",$channel->uid)->exists()){
            return $this->error("译文有数据无法删除");
        }
        if(DhammaTerm::where("channal",$channel->uid)->exists()){
            return $this->error("术语有数据无法删除");
        }
        if(WbwBlock::where("channel_uid",$channel->uid)->exists()){
            return $this->error("逐词解析有数据无法删除");
        }
        $delete = 0;
        DB::transaction(function() use($channel,$delete){
            //TODO 删除相关资源
            $delete = $channel->delete();
        });

        return $this->ok($delete);
    }
}
