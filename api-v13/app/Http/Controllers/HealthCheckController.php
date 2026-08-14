<?php

namespace App\Http\Controllers;

use App\Services\OpenSearchService;
use App\Services\RabbitMQService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HealthCheckController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
        if (file_exists(base_path('.stop'))) {
            return response()->json(
                ['createdAt' => now(), 'checks' => []],
                503,
                ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'],
                JSON_UNESCAPED_UNICODE
            );
        }

        $checks = [];
        $healthy = true;

        // Cache
        try {
            Cache::put('health_check', true, 5);
            Cache::get('health_check');
            $checks['cache'] = true;
        } catch (\Throwable $e) {
            $checks['cache'] = false;
            $healthy = false;
            Log::error('Health check failed: cache', ['error' => $e->getMessage()]);
        }

        // Database
        try {
            DB::connection()->getPdo();
            $checks['db'] = true;
        } catch (\Throwable $e) {
            $checks['db'] = false;
            $healthy = false;
            Log::error('Health check failed: db', ['error' => $e->getMessage()]);
        }

        // OpenSearch
        try {
            $service = app(OpenSearchService::class);
            [$ok, $msg] = $service->testConnection();
            if (! $ok) {
                throw new \Exception($msg);
            }
            $checks['opensearch'] = true;
        } catch (\Throwable $e) {
            $checks['opensearch'] = false;
            $healthy = false;
            Log::error('Health check failed: opensearch', ['error' => $e->getMessage()]);
        }

        // RabbitMQ
        try {
            $service = app(RabbitMQService::class);
            $service->isConnected() ? null : throw new \Exception('Not connected');
            $checks['rabbitmq'] = true;
        } catch (\Throwable $e) {
            $checks['rabbitmq'] = false;
            $healthy = false;
            Log::error('Health check failed: rabbitmq', ['error' => $e->getMessage()]);
        }

        return response()->json(
            [
                'createdAt' => now(),
                'services' => $checks,
                'host' => $_SERVER['HTTP_HOST'],
                'document-root' => $_SERVER['DOCUMENT_ROOT'],
            ],
            $healthy ? 200 : 500,
            ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'],
            JSON_UNESCAPED_UNICODE
        );
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
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
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
