<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSentenceAttachmentRequest;
use App\Http\Requests\UpdateSentenceAttachmentRequest;
use App\Http\Resources\SentenceAttachmentResource;
use App\Models\SentenceAttachment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SentenceAttachmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        switch ($request->view) {
            case 'sentence':
                $table = SentenceAttachment::where('sentence_id', $request->input('id'));
                break;
            default:
                return $this->error('known view');
                break;
        }

        $table->orderBy($request->input('order', 'updated_at'), $request->input('dir', 'desc'));
        $count = $table->count();
        $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 1000));

        $result = $table->get();

        return $this->ok([
            'rows' => SentenceAttachmentResource::collection($result),
            'count' => $count,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreSentenceAttachmentRequest  $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  SentenceAttachment  $sentenceAttachment
     * @return Response
     */
    public function show(Request $sentenceAttachment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateSentenceAttachmentRequest  $request
     * @return Response
     */
    public function update(Request $request, SentenceAttachment $sentenceAttachment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(SentenceAttachment $sentenceAttachment)
    {
        //
    }
}
