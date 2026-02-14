<?php

namespace Tests\Feature\Services;

use App\Models\Channel;
use App\Services\PacketService;
use App\Services\SentenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;
use ZipArchive;
use App\Http\Api\ChannelApi;


/**
 * PacketService单元测试
 *
 * 测试PacketService的数据导出和打包功能
 */
class PacketServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 巴利文channel
     */
    private Channel $paliChannel;

    /**
     * 译文channel
     */
    private Channel $translationChannel;

    /**
     * 测试用的editor_uid
     */
    private string $editorUid;

    /**
     * SentenceService实例
     */
    private SentenceService $sentenceService;

    /**
     * 设置测试环境
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 调试：检查数据库连接
        dump(config('database.default')); // 应该是 pgsql
        dump(config('database.connections.pgsql.database')); // 应该是 mint_test

        // 生成测试用的editor_uid
        $this->editorUid = Str::uuid()->toString();

        // 初始化SentenceService
        $this->sentenceService = app(SentenceService::class);

        // 创建测试用的channels
        $orgChannelId = ChannelApi::getSysChannel('_System_Pali_VRI_');
        $this->paliChannel = Channel::find($orgChannelId);

        $this->translationChannel = Channel::find('00ae2c48-c204-4082-ae79-79ba2740d506');
    }

    /**
     * 清理测试环境
     *
     * @return void
     */
    protected function tearDown(): void
    {
        // 清理生成的文件
        Storage::deleteDirectory('packet');
        Storage::deleteDirectory('temp');

        parent::tearDown();
    }

    /**
     * 测试基本的导出功能
     *
     * @return void
     */
    public function test_export_creates_zip_file(): void
    {
        // 创建测试数据
        $this->createTestSentences();

        // 执行导出
        $service = new PacketService(
            $this->paliChannel->uid,
            [$this->translationChannel->uid]
        );

        $zipPath = $service->export();

        // 断言ZIP文件已创建
        $this->assertTrue(Storage::exists($zipPath));
        $this->assertStringStartsWith('packet/training_data_', $zipPath);
        $this->assertStringEndsWith('.zip', $zipPath);
    }

    /**
     * 测试ZIP文件内容结构
     *
     * @return void
     */
    public function test_zip_contains_correct_structure(): void
    {
        // 创建测试数据
        $this->createTestSentences();

        // 执行导出
        $service = new PacketService(
            $this->paliChannel->uid,
            [$this->translationChannel->uid]
        );

        $zipPath = $service->export();
        $fullPath = Storage::path($zipPath);

        // 打开ZIP文件检查内容
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($fullPath));

        // 检查是否包含translations目录
        $expectedFile = 'translations/Chinese Translation.jsonl';
        $this->assertNotFalse($zip->locateName($expectedFile));

        $zip->close();
    }

    /**
     * 测试JSONL文件内容格式
     *
     * @return void
     */
    public function test_jsonl_file_format(): void
    {
        // 创建测试数据
        $this->createTestSentences();

        // 执行导出
        $service = new PacketService(
            $this->paliChannel->uid,
            [$this->translationChannel->uid]
        );

        $zipPath = $service->export();
        $fullPath = Storage::path($zipPath);

        // 解压并读取JSONL文件
        $zip = new ZipArchive();
        $zip->open($fullPath);

        $jsonlContent = $zip->getFromName('translations/Chinese Translation.jsonl');
        $zip->close();

        // 解析JSONL内容
        $lines = explode("\n", trim($jsonlContent));

        $this->assertGreaterThan(0, count($lines));

        // 检查第一行的格式
        $firstLine = json_decode($lines[0], true);

        $this->assertArrayHasKey('id', $firstLine);
        $this->assertArrayHasKey('pali', $firstLine);
        $this->assertArrayHasKey('translation', $firstLine);

        // 检查ID格式
        $this->assertMatchesRegularExpression('/^\d+-\d+-\d+-\d+$/', $firstLine['id']);
    }

    /**
     * 测试数据排序
     *
     * @return void
     */
    public function test_data_is_sorted_correctly(): void
    {
        // 创建乱序的测试数据
        $this->sentenceService->save([
            'book_id' => 2,
            'paragraph' => 1,
            'word_start' => 1,
            'word_end' => 5,
            'content' => 'Pali text 2',
            'channel_uid' => $this->paliChannel->uid,
            'editor_uid' => $this->editorUid,
        ]);

        $this->sentenceService->save([
            'book_id' => 2,
            'paragraph' => 1,
            'word_start' => 1,
            'word_end' => 5,
            'content' => 'Translation 2',
            'channel_uid' => $this->translationChannel->uid,
            'editor_uid' => $this->editorUid,
        ]);

        $this->sentenceService->save([
            'book_id' => 1,
            'paragraph' => 1,
            'word_start' => 1,
            'word_end' => 5,
            'content' => 'Pali text 1',
            'channel_uid' => $this->paliChannel->uid,
            'editor_uid' => $this->editorUid,
        ]);

        $this->sentenceService->save([
            'book_id' => 1,
            'paragraph' => 1,
            'word_start' => 1,
            'word_end' => 5,
            'content' => 'Translation 1',
            'channel_uid' => $this->translationChannel->uid,
            'editor_uid' => $this->editorUid,
        ]);

        // 执行导出
        $service = new PacketService(
            $this->paliChannel->uid,
            [$this->translationChannel->uid]
        );

        $zipPath = $service->export();
        $fullPath = Storage::path($zipPath);

        // 读取JSONL内容
        $zip = new ZipArchive();
        $zip->open($fullPath);
        $jsonlContent = $zip->getFromName('translations/Chinese Translation.jsonl');
        $zip->close();

        $lines = explode("\n", trim($jsonlContent));
        $firstLine = json_decode($lines[0], true);
        $secondLine = json_decode($lines[1], true);

        // 第一条应该是book_id=1的记录
        $this->assertEquals('1-1-1-5', $firstLine['id']);
        $this->assertEquals('2-1-1-5', $secondLine['id']);
    }

    /**
     * 测试跳过空译文
     *
     * @return void
     */
    public function test_skips_empty_translations(): void
    {
        // 创建有空译文的测试数据
        $this->sentenceService->save([
            'book_id' => 1,
            'paragraph' => 1,
            'word_start' => 1,
            'word_end' => 5,
            'content' => 'Pali text',
            'channel_uid' => $this->paliChannel->uid,
            'editor_uid' => $this->editorUid,
        ]);

        $this->sentenceService->save([
            'book_id' => 1,
            'paragraph' => 1,
            'word_start' => 1,
            'word_end' => 5,
            'content' => '', // 空译文
            'channel_uid' => $this->translationChannel->uid,
            'editor_uid' => $this->editorUid,
        ]);

        $this->sentenceService->save([
            'book_id' => 1,
            'paragraph' => 2,
            'word_start' => 1,
            'word_end' => 5,
            'content' => 'Pali text 2',
            'channel_uid' => $this->paliChannel->uid,
            'editor_uid' => $this->editorUid,
        ]);

        $this->sentenceService->save([
            'book_id' => 1,
            'paragraph' => 2,
            'word_start' => 1,
            'word_end' => 5,
            'content' => 'Valid translation',
            'channel_uid' => $this->translationChannel->uid,
            'editor_uid' => $this->editorUid,
        ]);

        // 执行导出
        $service = new PacketService(
            $this->paliChannel->uid,
            [$this->translationChannel->uid]
        );

        $zipPath = $service->export();
        $fullPath = Storage::path($zipPath);

        // 读取JSONL内容
        $zip = new ZipArchive();
        $zip->open($fullPath);
        $jsonlContent = $zip->getFromName('translations/Chinese Translation.jsonl');
        $zip->close();

        $lines = array_filter(explode("\n", trim($jsonlContent)));

        // 应该只有一条记录(跳过了空译文)
        $this->assertCount(1, $lines);
    }

    /**
     * 测试多个译文版本
     *
     * @return void
     */
    public function test_multiple_translation_channels(): void
    {
        // 创建第二个译文channel
        $secondTranslation = Channel::create([
            'uid' => 'translation-2-test-uid',
            'name' => 'English Translation',
            'lang' => 'en',
        ]);

        // 创建测试数据
        $this->sentenceService->save([
            'book_id' => 1,
            'paragraph' => 1,
            'word_start' => 1,
            'word_end' => 5,
            'content' => 'Pali text',
            'channel_uid' => $this->paliChannel->uid,
            'editor_uid' => $this->editorUid,
        ]);

        $this->sentenceService->save([
            'book_id' => 1,
            'paragraph' => 1,
            'word_start' => 1,
            'word_end' => 5,
            'content' => 'Chinese translation',
            'channel_uid' => $this->translationChannel->uid,
            'editor_uid' => $this->editorUid,
        ]);

        $this->sentenceService->save([
            'book_id' => 1,
            'paragraph' => 1,
            'word_start' => 1,
            'word_end' => 5,
            'content' => 'English translation',
            'channel_uid' => $secondTranslation->uid,
            'editor_uid' => $this->editorUid,
        ]);

        // 执行导出
        $service = new PacketService(
            $this->paliChannel->uid,
            [
                $this->translationChannel->uid,
                $secondTranslation->uid
            ]
        );

        $zipPath = $service->export();
        $fullPath = Storage::path($zipPath);

        // 检查ZIP包含两个文件
        $zip = new ZipArchive();
        $zip->open($fullPath);

        $this->assertNotFalse($zip->locateName('translations/Chinese Translation.jsonl'));
        $this->assertNotFalse($zip->locateName('translations/English Translation.jsonl'));

        $zip->close();
    }

    /**
     * 测试channel不存在时使用uid作为文件名
     *
     * @return void
     */
    public function test_uses_uid_when_channel_not_found(): void
    {
        // 创建测试数据,使用不存在的channel_uid
        $nonExistentUid = 'non-existent-uid';

        $this->sentenceService->save([
            'book_id' => 1,
            'paragraph' => 1,
            'word_start' => 1,
            'word_end' => 5,
            'content' => 'Pali text',
            'channel_uid' => $this->paliChannel->uid,
            'editor_uid' => $this->editorUid,
        ]);

        $this->sentenceService->save([
            'book_id' => 1,
            'paragraph' => 1,
            'word_start' => 1,
            'word_end' => 5,
            'content' => 'Translation',
            'channel_uid' => $nonExistentUid,
            'editor_uid' => $this->editorUid,
        ]);

        // 执行导出
        $service = new PacketService(
            $this->paliChannel->uid,
            [$nonExistentUid]
        );

        $zipPath = $service->export();
        $fullPath = Storage::path($zipPath);

        // 检查使用uid作为文件名
        $zip = new ZipArchive();
        $zip->open($fullPath);

        $expectedFile = "translations/{$nonExistentUid}.jsonl";
        $this->assertNotFalse($zip->locateName($expectedFile));

        $zip->close();
    }

    /**
     * 测试临时文件清理
     *
     * @return void
     */
    public function test_cleanup_temp_files(): void
    {
        // 创建测试数据
        $this->createTestSentences();

        // 执行导出
        $service = new PacketService(
            $this->paliChannel->uid,
            [$this->translationChannel->uid]
        );

        $service->export();

        // 检查临时目录已被清理
        $tempPath = storage_path('app/temp/packet');
        $this->assertDirectoryDoesNotExist($tempPath);
    }

    /**
     * 创建基础测试数据
     *
     * @return void
     */
    private function createTestSentences(): void
    {
        $this->sentenceService->save([
            'book_id' => 1,
            'paragraph' => 1,
            'word_start' => 1,
            'word_end' => 5,
            'content' => 'Pali sentence content',
            'channel_uid' => $this->paliChannel->uid,
            'editor_uid' => $this->editorUid,
        ]);

        $this->sentenceService->save([
            'book_id' => 1,
            'paragraph' => 1,
            'word_start' => 1,
            'word_end' => 5,
            'content' => 'Chinese translation content',
            'channel_uid' => $this->translationChannel->uid,
            'editor_uid' => $this->editorUid,
        ]);
    }
}
