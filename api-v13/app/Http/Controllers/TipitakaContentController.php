<?php

namespace App\Http\Controllers;

use App\DTO\Search\HitItemDTO;
use App\Http\Api\ChannelApi;
use App\Services\OpenSearchService;
use Illuminate\Http\Request;

class TipitakaContentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        //
        $channelId = $request->input('channel', ChannelApi::getSysChannel('_System_Pali_VRI_'));
        $openSearchId = "tipitaka_chapter_{$id}_{$channelId}";

        try {
            $doc = HitItemDTO::fromArray(app(OpenSearchService::class)->get($openSearchId))->toArray();
        } catch (\Throwable $th) {

            return $this->error('resouce invalid'.$th->getMessage());
        }

        $display = $doc['display'] ?? '';

        return $this->ok($display);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
