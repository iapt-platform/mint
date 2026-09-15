<?php

namespace App\Http\Controllers;

use App\Models\TaskAssignee;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TaskAssigneeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
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
    public function show(TaskAssignee $taskAssignee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, TaskAssignee $taskAssignee)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(TaskAssignee $taskAssignee)
    {
        //
        $del = $taskAssignee->delete();

        return $this->ok($del);
    }
}
