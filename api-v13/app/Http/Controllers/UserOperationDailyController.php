<?php

namespace App\Http\Controllers;

use App\Http\Api\UserApi;
use App\Models\UserOperationDaily;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserOperationDailyController extends Controller
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
            case 'user-all':
                $queryUserUuid = UserApi::getIdByName($request->input('studio_name'));
                $user = AuthService::current($request);
                if (! $user) {
                    return $this->error(__('auth.failed'));
                }
                // TODO 判断是否有查看权限
                if ($queryUserUuid !== $user['user_uid']) {
                    return $this->error(__('auth.failed'));
                }
                $result = UserOperationDaily::where('user_id', $user['user_id'])
                    ->select(['date_int', 'duration', 'hit'])
                    ->orderBy('date_int')
                    ->get();
                break;
            case 'user-year':
                $queryUserId = UserApi::getIntIdByName($request->input('studio_name'));
                // TODO 判断是否有查看权限
                $result = UserOperationDaily::where('user_id', $queryUserId)
                    ->select(['date_int', 'duration'])
                    ->orderBy('date_int')
                    ->get();
                break;
        }

        return $this->ok(['rows' => $result, 'count' => count($result)]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
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
     * @return Response
     */
    public function show(UserOperationDaily $userOperationDaily)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(UserOperationDaily $userOperationDaily)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, UserOperationDaily $userOperationDaily)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(UserOperationDaily $userOperationDaily)
    {
        //
    }
}
