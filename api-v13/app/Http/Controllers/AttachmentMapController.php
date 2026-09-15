<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentMapRequest;
use App\Http\Requests\UpdateAttachmentMapRequest;
use App\Models\AttachmentMap;
use Illuminate\Http\Response;

class AttachmentMapController extends Controller
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
    public function store(StoreAttachmentMapRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(AttachmentMap $attachmentMap)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(UpdateAttachmentMapRequest $request, AttachmentMap $attachmentMap)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(AttachmentMap $attachmentMap)
    {
        //
    }
}
