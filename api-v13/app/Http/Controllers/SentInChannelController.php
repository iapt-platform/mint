<?php

namespace App\Http\Controllers;

use App\Http\Api\ChannelApi;
use App\Models\Sentence;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class SentInChannelController extends Controller
{
    /**
     * 用channel 和句子编号列表查询句子
     *
     * @return Response
     */
    public function index(Request $request)
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
        $sent = $request->input('sentences');
        $query = [];
        foreach ($sent as $value) {
            // code...
            $ids = explode('-', $value);
            if (count($ids) === 4) {
                $query[] = $ids;
            }
        }
        $channelsQuery = [];
        $channelsInput = $request->input('channels');
        foreach ($channelsInput as $value) {
            if (Str::isUuid($value)) {
                $channelsQuery[] = $value;
            } else {
                $channelId = ChannelApi::getSysChannel($value);
                if ($channelId) {
                    $channelsQuery[] = $channelId;
                }
            }
        }

        $table = Sentence::select([
            'id',
            'book_id',
            'paragraph',
            'word_start',
            'word_end',
            'content',
            'content_type',
            'editor_uid',
            'channel_uid',
            'updated_at',
        ])
            ->whereIn('channel_uid', $channelsQuery)
            ->whereIns(['book_id', 'paragraph', 'word_start', 'word_end'], $query);
        $result = $table->get();

        return $this->ok(['rows' => $result, 'count' => count($result)]);
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Sentence $sentence)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, Sentence $sentence)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Sentence $sentence)
    {
        //
    }
}
