<?php

namespace App\Http\Controllers;

use App\Services\OpenSearchService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * 更新 pali 同义词文件版本（运维接口）
 *
 * 鉴权：请求头 Authorization: Bearer <APP_OPS_TOKEN>，由 ops.token 中间件校验，
 * 不匹配返回 403。
 *
 * 请求：
 *   PUT /api/ops/update-pali-synonyms/20260731
 *
 * 返回：
 * {
 *   "ok": true,
 *   "data": {
 *     "index": "wikipali_20260516",
 *     "version": "20260731",
 *     "synonyms_path": "analysis/pali-synonyms-20260731.txt"
 *   },
 *   "message": ""
 * }
 */
class UpdatePaliSynonymsController extends Controller
{
    public function __construct(protected OpenSearchService $searchService) {}

    /**
     * 更新索引使用的 pali 同义词文件版本
     *
     * 将 pali_synonyms filter 的 synonyms_path 指向
     * analysis/pali-synonyms-{$version}.txt，该文件须已上传到
     * OpenSearch 各节点的 config 目录。
     *
     * @param  string  $version  同义词文件版本号，例如 20260731
     */
    public function update(string $version): JsonResponse
    {
        try {
            $result = $this->searchService->updatePaliSynonymsPath($version);

            return $this->ok([
                'index' => config('mint.opensearch.index'),
                'version' => $version,
                'synonyms_path' => $result['path'],
            ]);
        } catch (Exception $e) {
            Log::error('UpdatePaliSynonymsController::update', [
                'version' => $version,
                'message' => $e->getMessage(),
            ]);

            return $this->error('更新同义词文件失败：'.$e->getMessage(), null, 500);
        }
    }
}
