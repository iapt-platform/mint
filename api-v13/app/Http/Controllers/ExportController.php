<?php

namespace App\Http\Controllers;

use App\Http\Api\Mq;
use App\Services\AuthService;
use App\Tools\ExportDownload;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $queryId = Str::uuid();
        $token = AuthService::getToken($request);
        switch ($request->input('type', 'chapter')) {
            case 'chapter':
                $data = [
                    'book' => $request->input('book'),
                    'para' => $request->input('par'),
                    'channel' => $request->input('channel'),
                    'format' => $request->input('format'),
                    'origin' => $request->input('origin'),
                    'translation' => $request->input('translation'),
                    'queryId' => $queryId,
                ];
                if ($token) {
                    $data['token'] = $token;
                }
                Mq::publish('export_pali_chapter', $data);
                break;
            case 'article':
                $data = [
                    'id' => $request->input('id'),
                    'channel' => $request->input('channel'),
                    'format' => $request->input('format'),
                    'origin' => $request->input('origin'),
                    'translation' => $request->input('translation'),
                    'queryId' => $queryId,
                    'anthology' => $request->input('anthology'),
                    'channel' => $request->input('channel'),
                ];
                if ($token) {
                    $data['token'] = $token;
                }
                Mq::publish('export_article', $data);
                break;
            default:
                return $this->error('unknown type '.$request->input('type'), 400, 400);
                break;
        }

        return $this->ok($queryId);
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
     * @param  string  $id
     * @return Response
     */
    public function show($filename)
    {
        //
        $exportChapter = new ExportDownload(['queryId' => $filename]);
        $exportStatus = $exportChapter->getStatus();
        if (empty($exportStatus)) {
            return $this->error('no file', 200, 200);
        }

        $output = [];
        $output['status'] = $exportStatus;
        if ($exportStatus['progress'] === 1) {
            $output['url'] = $exportStatus['url'];
        }

        return $this->ok($output);
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
