<?php

namespace App\Http\Controllers;

use App\Http\Api\ShareApi;
use App\Http\Api\StudioApi;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StudioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->input('view')) {
            case 'collaboration-channel':
                // 协作channel 拥有者列表
                $studioId = StudioApi::getIdByName($request->input('studio_name'));
                $resList = ShareApi::getResList($studioId, 2);
                $resId = [];
                foreach ($resList as $res) {
                    $resId[] = $res['res_id'];
                }
                $owners = Channel::whereIn('uid', $resId)
                    ->where('owner_uid', '<>', $studioId)
                    ->select('owner_uid')
                    ->groupBy('owner_uid')->get();
                $output = [];
                foreach ($owners as $key => $owner) {
                    // code...
                    $output[] = StudioApi::getById($owner->owner_uid);
                }

                return $this->ok(['rows' => $output, 'count' => count($output)]);
                break;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
