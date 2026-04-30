<?php

namespace App\Http\Controllers;

use App\Models\TaskAssignee;
use Illuminate\Http\Request;

class TaskAssigneeController extends Controller
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
     * @param  \App\Models\TaskAssignee  $taskAssignee
     * @return \Illuminate\Http\Response
     */
    public function show(TaskAssignee $taskAssignee)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TaskAssignee  $taskAssignee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TaskAssignee $taskAssignee)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TaskAssignee  $taskAssignee
     * @return \Illuminate\Http\Response
     */
    public function destroy(TaskAssignee $taskAssignee)
    {
        //
        $del = $taskAssignee->delete();
        return $this->ok($del);
    }
}
