<?php

namespace App\Http\Controllers;

use App\Models\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ViewController extends Controller
{
    private function getTargetId($request){
        $target_id = FALSE;
        switch ($request->get("target_type")) {
            case 'chapter-instance':
                # code...
                $target_id = $request->get("target_id");
                break;
            case 'chapter':
                # code...
                break;
            case 'article-instance':
                # code...
                break;
            case 'article':
                # code...
                break;
            default:
                $target_id = $request->get("target_id");
                # code...
                break;
        }
        if(Str::isUuid($target_id)){
            return $target_id;
        }else{
            return false;
        }
        
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        //根据target type 获取 target id
        $target_id = $this->getTargetId($request);
        if($target_id){
            $count = View::where("target_id",$target_id)->count();
        }else{
            $count = 0;
        }
        return $this->ok($count);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /*
        $rules = array(
            'target_type' => 'required'
        );
        $validator = Validator::make($request->all(), $rules);

        // process the login
        if ($validator->fails()) {
            return $this->error($validator);
        }
*/
        //根据target type 获取 target id
        $target_id = $this->getTargetId($request);
        $clientIp = request()->ip();
        $param = [
            'target_id' => $target_id,
            'target_type' => $request->get("target_type"),
        ];
        if(isset($GET['user_uid'])){
            //已经登陆
            $user_id = $GET['user_uid'];
            $param['user_id'] = $user_id;
        }else{
            $param['user_ip'] = $clientIp;
        }
        $new = View::firstOrNew($param);
        $new->user_ip = $clientIp;
        $new->save();

        return $this->ok($new);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\View  $view
     * @return \Illuminate\Http\Response
     */
    public function show(View $view)
    {
        //

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\View  $view
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, View $view)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\View  $view
     * @return \Illuminate\Http\Response
     */
    public function destroy(View $view)
    {
        //
    }
}
