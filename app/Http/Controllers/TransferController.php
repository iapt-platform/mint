<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use App\Models\Channel;
use App\Models\Article;
use Illuminate\Http\Request;
use App\Http\Api\AuthApi;
use App\Http\Api\StudioApi;
use App\Http\Api\UserApi;
use App\Http\Resources\TransferResource;

class TransferController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
		switch ($request->get('view')) {
            case 'studio':
                # 获取studio内所有channel
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'));
                }
                //判断当前用户是否有指定的studio的管理权限
                $studioId = StudioApi::getIdByName($request->get('name'));
                if($user['user_uid'] !== $studioId){
                    return $this->error(__('auth.failed'));
                }
                switch ($request->get('view2')) {
                    case 'in':
                        $table = Transfer::where('new_owner',$studioId);
                        break;
                    case 'out':
                        $table = Transfer::where('origin_owner',$studioId);
                        break;
                    default:
                        return $this->error('no view2');
                    break;
                }
                $outNumber = Transfer::where('origin_owner',$studioId)
                                    ->where('status','transferred')
                                    ->count();
                $inNumber = Transfer::where('new_owner',$studioId)
                                    ->where('status','transferred')
                                    ->count();
                break;
        }
        if(!empty($search)){
            $table->where('title', 'like', $search."%");
        }
        $table->orderBy($request->get('order','updated_at'),
                        $request->get('dir','desc'));
        $count = $table->count();
        $table->skip($request->get("offset",0))
              ->take($request->get('limit',100));

        $result = $table->get();


		return $this->ok(["rows"=>TransferResource::collection($result),
                              "count"=>$count,
                              'out'=>$outNumber,
                              'in'=>$inNumber,
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
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //
        // validate
        // read more on validation at http://laravel.com/docs/validation


        $rules = array(
            'res_id' => 'required',
            'res_type' => 'required',
            'new_owner' => 'required',
        );


        $validated = $request->validate($rules);

        $resId = $request->get('res_id');
        foreach ($resId as $id) {
            $transfer = new Transfer;
            //查看权限
            switch ($request->get('res_type')) {
                case 'channel':
                    $oldRes = Channel::find($id);
                    if($oldRes->owner_uid !== $user['user_uid']){
                        return $this->error(__('auth.failed'),[403],403);
                    }
                    $transfer->origin_owner = $oldRes->owner_uid;
                    break;
                case 'article':
                    $oldRes = Article::find($id);
                    if($oldRes->owner !== $user['user_uid']){
                        return $this->error(__('auth.failed'),[403],403);
                    }
                    $transfer->origin_owner = $oldRes->owner;
                    break;
                default:
                    # code...
                    break;
            }
            //查重
            if(Transfer::where('res_id',$id)
                    ->where('res_type',$request->get('res_type'))
                    ->where('status','transferred')
                    ->exists()){
                        return $this->error('该资源已经进入转让流程',[200],200);
                    }
            $transfer->res_id = $id;
            $transfer->res_type = $request->get('res_type');

            $transfer->transferor_id = $user['user_uid'];
            $transfer->new_owner = $request->get('new_owner');
            $transfer->save();
        }

        return $this->ok(count($resId));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transfer  $transfer
     * @return \Illuminate\Http\Response
     */
    public function show(Transfer $transfer)
    {
        //
        return $this->ok(new TransferResource($discussion));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transfer  $transfer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transfer $transfer)
    {
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'),[403],403);
        }
        //权限
        switch ($request->get('status')) {
            case 'accept':
            case 'refuse':
                if($transfer->new_owner!==$user['user_uid']){
                    return $this->error(__('auth.failed'),[403],403);
                }
                $transfer->status = $request->get('status');
                break;
            case 'cancel':
                if($transfer->origin_owner!==$user['user_uid']){
                    return $this->error(__('auth.failed'),[403],403);
                }
                $transfer->status = 'cancel';
                break;
            default:
                return $this->error(__('auth.failed'),[404],404);
                break;
        }
        $transfer->editor_id = $user['user_uid'];
        $transfer->save();
        if($request->get('status')==='accept'){
            switch ($transfer->res_type) {
                case 'channel':
                    Channel::where('uid',$transfer->res_id)
                            ->update(['owner_uid'=>$transfer->new_owner]);
                    break;
                case 'article':
                    $userId = UserApi::getIdByUuid($transfer->new_owner);
                    Article::where('uid',$transfer->res_id)
                            ->update(['owner'=>$transfer->new_owner,'owner_id'=>$userId]);
                    break;
                default:
                    # code...
                    break;
            }
        }
        return $this->ok(new TransferResource($transfer));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transfer  $transfer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transfer $transfer)
    {
        //
    }
}
