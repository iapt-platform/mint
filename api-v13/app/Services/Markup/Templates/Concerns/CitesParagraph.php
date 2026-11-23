<?php

namespace App\Services\Markup\Templates\Concerns;

use App\Services\Markup\Ir\Call;
use App\Services\PaliSeriesesService;

/**
 * 嵌入经文段落 / 章节的模板（{{para}}、{{chapter}}、{{article|type=chapter|para}}）的标题规则：
 * `style=reference`（行内引用）且没给标题时，标题用 wikipali 缩写 + 段号（如 `dī.ni.1.168`），
 * 查不到缩写时用 id 本身。
 */
trait CitesParagraph
{
    /**
     * @param  string  $id  段落 / 章节 id（`book-para`）
     */
    protected function citedTitle(Call $call, string $id): string
    {
        $title = (string) $call->param('title');
        if ($title !== '' || (string) $call->param('style') !== 'reference') {
            return $title;
        }
        $ref = self::paragraphRef($id)[0] ?? null;
        if ($ref === null) {
            return $id;
        }

        return app(PaliSeriesesService::class)->citation($ref[0], $ref[1]) ?? $id;
    }
}
