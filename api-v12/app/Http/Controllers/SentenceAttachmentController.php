<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSentenceAttachmentRequest;
use App\Http\Requests\UpdateSentenceAttachmentRequest;
use Illuminate\Http\Request;
use App\Models\SentenceAttachment;
use App\Http\Resources\SentenceAttachmentResource;

class SentenceAttachmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        switch ($request->view) {
            case 'sentence':
                $table = SentenceAttachment::where('sentence_id', $request->get('id'));
                break;
            default:
                return $this->error('known view');
                break;
        }

        $table->orderBy($request->get('order', 'updated_at'), $request->get('dir', 'desc'));
        $count = $table->count();
        $table->skip($request->get("offset", 0))
            ->take($request->get('limit', 1000));

        $result = $table->get();
        return $this->ok([
            "rows" => SentenceAttachmentResource::collection($result),
            "count" => $count
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSentenceAttachmentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SentenceAttachment  $sentenceAttachment
     * @return \Illuminate\Http\Response
     */
    public function show(Request $sentenceAttachment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSentenceAttachmentRequest  $request
     * @param  \App\Models\SentenceAttachment  $sentenceAttachment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SentenceAttachment $sentenceAttachment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SentenceAttachment  $sentenceAttachment
     * @return \Illuminate\Http\Response
     */
    public function destroy(SentenceAttachment $sentenceAttachment)
    {
        //
    }
}
