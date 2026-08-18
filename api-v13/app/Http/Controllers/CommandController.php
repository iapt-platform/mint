<?php

namespace App\Http\Controllers;

use App\Http\Api\Mq;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CommandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
        return $this->ok('ok');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
        $user = AuthService::current($request);
        if (! $user || $user['user_uid'] !== 'ba5463f3-72d1-4410-858e-eadd10884713') {
            return $this->error(__('auth.failed'), 403, 403);
        }

        Mq::publish('task', [
            'name' => $request->input('name'),
            'param' => $request->input('param'),
        ]);

        return $this->ok('ok');
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
