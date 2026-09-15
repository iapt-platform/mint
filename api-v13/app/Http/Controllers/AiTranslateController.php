<?php

namespace App\Http\Controllers;

use App\Models\PaliText;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiTranslateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {}

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
        return $this->fetch(strip_tags($request->input('origin')));
    }

    private function fetch($origin, $engin = 'kimi', $prompt_pre = '', $prompt_suf = '请翻译上述巴利文。')
    {
        $api = config('mint.ai.accounts');
        $selected = array_filter($api, function ($value) use ($engin) {
            return $value['name'] === $engin;
        });
        if (! is_array($selected) || count($selected) === 0) {
            return $this->error('no engin name', 200, 200);
        }

        $url = $selected[0]['api_url'];
        $param = [
            'model' => $selected[0]['model'],
            'messages' => [
                ['role' => 'system', 'content' => '你是翻译人工智能助手，bhikkhu 为专有名词，不可翻译成其他语言。'],
                ['role' => 'user', 'content' => "{$prompt_pre}{$origin}\n{$prompt_suf}"],
            ],
            'temperature' => 0.3,
        ];
        $response = Http::withToken($selected[0]['token'])
            ->post($url, $param);
        if ($response->failed()) {
            $this->error('http request error'.$response->json('message'));
            Log::error('http request error', ['data' => $response->json()]);

            return $this->error($response->json(), 200, 200);
        } else {
            return $this->ok($response->json());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return Response
     */
    public function show(Request $request, $id)
    {
        //
        $para = explode('-', $id);
        if (count($para) >= 2) {
            $content = PaliText::where('book', $para[0])
                ->where('paragraph', $para[1])
                ->value('text');
            if (! empty($content)) {
                return $this->fetch($content, $request->input('engin', config('mint.ai.default')));
            } else {
                return $this->error('no content', 200, 200);
            }
        } else {
            return $this->error('参数错误', 403, 403);
        }
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
