<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentMapRequest;
use App\Http\Requests\UpdateAttachmentMapRequest;
use App\Models\AttachmentMap;

class AttachmentMapController extends Controller
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
     * @param  \App\Http\Requests\StoreAttachmentMapRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAttachmentMapRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AttachmentMap  $attachmentMap
     * @return \Illuminate\Http\Response
     */
    public function show(AttachmentMap $attachmentMap)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAttachmentMapRequest  $request
     * @param  \App\Models\AttachmentMap  $attachmentMap
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAttachmentMapRequest $request, AttachmentMap $attachmentMap)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AttachmentMap  $attachmentMap
     * @return \Illuminate\Http\Response
     */
    public function destroy(AttachmentMap $attachmentMap)
    {
        //
    }
}
