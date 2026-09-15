<?php

namespace App\Http\Controllers;

use App\Http\Api\ChannelApi;
use App\Models\DhammaTerm;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GrammarGuideController extends Controller
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
     * @param  DhammaTerm  $dhammaTerm
     * @return Response
     */
    public function show(string $id)
    {
        //
        $param = explode('_', $id);

        $localTermChannel = ChannelApi::getSysChannel(
            '_System_Grammar_Term_'.strtolower($param[1]).'_',
            '_System_Grammar_Term_en_'
        );
        if (! $localTermChannel) {
            return $this->error('no term channel');
        }
        $result = DhammaTerm::where('word', $param[0])
            ->where('channal', $localTermChannel)->first();

        if ($result) {
            return $this->ok("# {$result->meaning}\n {$result->note}");
        } else {
            return $this->ok("# {$id}\n no record");
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, DhammaTerm $dhammaTerm)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(DhammaTerm $dhammaTerm)
    {
        //
    }
}
