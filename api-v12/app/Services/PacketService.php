<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\Sentence;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use App\Http\Api\ChannelApi;

/**
 * PacketService
 *
 * 用于导出句子数据为训练数据包的服务类
 * 将指定版本的译文与巴利原文配对导出为JSONL格式,并打包为ZIP文件
 */
class PacketService
{
    /**
     * 每批处理的记录数
     */
    private const CHUNK_SIZE = 1000;

    /**
     * 临时文件存储路径
     */
    private const TEMP_DIR = 'temp/packet';

    /**
     * 巴利原文的channel_uid
     */
    private string $paliChannelUid;

    /**
     * 译文版本的channel_uid数组
     */
    private array $translationChannelUids;

    /**
     * 临时文件路径集合
     */
    private array $tempFiles = [];

    /**
     * 构造函数
     *
     * @param string $paliChannelUid 巴利原文的channel_uid
     * @param array $translationChannelUids 译文版本的channel_uid数组
     */
    public function __construct(array $translationChannelUids)
    {
        $this->paliChannelUid = ChannelApi::getSysChannel('_System_Pali_VRI_');
        $this->translationChannelUids = $translationChannelUids;
    }

    /**
     * 执行导出并打包
     *
     * @return string 返回生成的ZIP文件路径
     * @throws \Exception
     */
    public function export(): string
    {
        try {
            // 创建临时目录
            $this->createTempDirectory();

            // 导出所有译文文件
            foreach ($this->translationChannelUids as $channelUid) {
                $this->exportTranslation($channelUid);
            }

            // 打包ZIP文件
            $zipPath = $this->createZipArchive();

            // 清理临时文件
            $this->cleanupTempFiles();

            return $zipPath;
        } catch (\Exception $e) {
            // 发生错误时也要清理临时文件
            $this->cleanupTempFiles();
            throw $e;
        }
    }

    /**
     * 创建临时目录
     *
     * @return void
     */
    private function createTempDirectory(): void
    {
        $tempPath = storage_path('app/' . self::TEMP_DIR);

        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0755, true);
        }

        // 创建translations子目录
        $translationsPath = $tempPath . '/translations';
        if (!is_dir($translationsPath)) {
            mkdir($translationsPath, 0755, true);
        }
    }

    /**
     * 导出指定译文版本的数据
     *
     * @param string $channelUid 译文版本的channel_uid
     * @return void
     */
    private function exportTranslation(string $channelUid): void
    {
        // 获取channel名称
        $channelName = $this->getChannelName($channelUid);

        // 创建JSONL文件
        $filename = $channelName . '.jsonl';
        $filepath = storage_path('app/' . self::TEMP_DIR . '/translations/' . $filename);

        // 记录临时文件路径
        $this->tempFiles[] = $filepath;

        // 打开文件准备写入
        $handle = fopen($filepath, 'w');

        if ($handle === false) {
            throw new \RuntimeException("无法创建文件: {$filepath}");
        }

        try {
            // 分批查询并写入数据
            $this->writeTranslationData($handle, $channelUid);
        } finally {
            fclose($handle);
        }
    }

    /**
     * 查询并写入译文数据
     *
     * @param resource $handle 文件句柄
     * @param string $channelUid 译文版本的channel_uid
     * @return void
     */
    private function writeTranslationData($handle, string $channelUid): void
    {
        // 构建查询,联表获取译文和巴利文
        DB::table('sentences as s1')
            ->select([
                's1.book_id',
                's1.paragraph',
                's1.word_start',
                's1.word_end',
                's1.content as translation',
                's2.content as pali'
            ])
            ->join('sentences as s2', function ($join) {
                $join->on('s1.book_id', '=', 's2.book_id')
                    ->on('s1.paragraph', '=', 's2.paragraph')
                    ->on('s1.word_start', '=', 's2.word_start')
                    ->on('s1.word_end', '=', 's2.word_end')
                    ->where('s2.channel_uid', '=', $this->paliChannelUid);
            })
            ->where('s1.channel_uid', '=', $channelUid)
            ->whereNotNull('s1.content')
            ->where('s1.content', '!=', '')
            ->orderBy('s1.book_id')
            ->orderBy('s1.paragraph')
            ->orderBy('s1.word_start')
            ->orderBy('s1.word_end')
            ->chunk(self::CHUNK_SIZE, function ($sentences) use ($handle) {
                foreach ($sentences as $sentence) {
                    // 如果没有译文,跳过
                    if (empty($sentence->translation)) {
                        continue;
                    }

                    // 构建ID
                    $id = sprintf(
                        '%s-%s-%s-%s',
                        $sentence->book_id,
                        $sentence->paragraph,
                        $sentence->word_start,
                        $sentence->word_end
                    );

                    // 构建JSON对象
                    $data = [
                        'id' => $id,
                        'pali' => $sentence->pali ?? '',
                        'translation' => $sentence->translation
                    ];

                    // 写入JSONL格式(每行一个JSON对象)
                    fwrite($handle, json_encode($data, JSON_UNESCAPED_UNICODE) . "\n");
                }
            });
    }

    /**
     * 获取channel名称
     *
     * @param string $channelUid channel的uuid
     * @return string channel名称,如果找不到则返回uuid
     */
    private function getChannelName(string $channelUid): string
    {
        $channel = Channel::where('uid', $channelUid)->first();

        return $channel?->name ?? $channelUid;
    }

    /**
     * 创建ZIP压缩包
     *
     * @return string 返回ZIP文件在Storage中的路径
     * @throws \RuntimeException
     */
    private function createZipArchive(): string
    {
        $timestamp = now()->format('YmdHis');
        $zipFilename = "training_data_{$timestamp}.zip";
        $zipPath = storage_path('app/packet/' . $zipFilename);

        // 确保packet目录存在
        $packetDir = storage_path('app/packet');
        if (!is_dir($packetDir)) {
            mkdir($packetDir, 0755, true);
        }

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("无法创建ZIP文件: {$zipPath}");
        }

        try {
            // 添加所有JSONL文件到ZIP
            $translationsDir = storage_path('app/' . self::TEMP_DIR . '/translations');

            if (is_dir($translationsDir)) {
                $files = scandir($translationsDir);

                foreach ($files as $file) {
                    if ($file === '.' || $file === '..') {
                        continue;
                    }

                    $filePath = $translationsDir . '/' . $file;

                    if (is_file($filePath)) {
                        // 添加到ZIP的translations目录下
                        $zip->addFile($filePath, 'translations/' . $file);
                    }
                }
            }

            $zip->close();
        } catch (\Exception $e) {
            $zip->close();
            throw $e;
        }

        // 返回相对于Storage的路径
        return 'packet/' . $zipFilename;
    }

    /**
     * 清理临时文件和目录
     *
     * @return void
     */
    private function cleanupTempFiles(): void
    {
        $tempPath = storage_path('app/' . self::TEMP_DIR);

        if (is_dir($tempPath)) {
            $this->deleteDirectory($tempPath);
        }
    }

    /**
     * 递归删除目录
     *
     * @param string $dir 目录路径
     * @return void
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;

            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }
}
