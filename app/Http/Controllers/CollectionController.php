<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Http\Request;

require_once __DIR__.'/../../../public/app/ucenter/function.php';


class CollectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
                //
		$result=false;
		$indexCol = ['uid','title','subtitle','summary','article_list','owner','lang','updated_at','created_at'];
		switch ($request->get('view')) {
            case 'studio_list':
		        $indexCol = ['owner'];
                $table = Collection::select($indexCol)->selectRaw('count(*) as count')->where('status', 30)->groupBy('owner');
                break;
			case 'studio':
				# code...
				$table = Collection::select($indexCol)->where('owner', $_COOKIE["user_uid"]);
				break;
			case 'public':
				$table = Collection::select($indexCol)->where('status', 30);
				break;
			default:
				# code...
			    return $this->error("没有查询到数据");
				break;
		}
        if(isset($_GET["search"])){
            $table = $table->where('title', 'like', $_GET["search"]."%");
        }
        $count = $table->count();
        if(isset($_GET["order"]) && isset($_GET["dir"])){
            $table = $table->orderBy($_GET["order"],$_GET["dir"]);
        }else{
            if($request->get('view') === 'studio_list'){
                $table = $table->orderBy('count','desc');
            }else{
                $table = $table->orderBy('updated_at','desc');
            }
        }

        if(isset($_GET["limit"])){
            $offset = 0;
            if(isset($_GET["offset"])){
                $offset = $_GET["offset"];
            }
            $table = $table->skip($offset)->take($_GET["limit"]);
        }
        $result = $table->get();
		if($result){
            $userinfo = new \UserInfo();
            foreach ($result as $key => $value) {
                # code...
                if(isset($result[$key]->article_list)){
                    $result[$key]->article_list = array_slice(\json_decode($value->article_list),0,4);
                }
                $value->studio = [
                    'id'=>$value->owner,
                    'name'=>$userinfo->getName($value->owner)['nickname'],
                    'avastar'=>'',
                    'owner' => [
                        'id'=>$value->owner,
                        'name'=>$userinfo->getName($value->owner)['nickname'],
                        'avastar'=>'',
                    ]
                ];
            }
			return $this->ok(["rows"=>$result,"count"=>$count]);
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
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
		$indexCol = ['uid','title','subtitle','summary','article_list','owner','lang','updated_at','created_at'];

		$result  = Collection::select($indexCol)->where('uid', $id)->first();
		if($result){
			if(!empty($result->article_list)){
				$result->article_list = \json_decode($result->article_list);
			}
			return $this->ok($result);
		}else{
			return $this->error("没有查询到数据");
		}
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Collection  $collection
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Collection $collection)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Collection  $collection
     * @return \Illuminate\Http\Response
     */
    public function destroy(Collection $collection)
    {
        //
    }
}
