<?php

namespace App\Services\Markup\Resolve;

use App\Models\Article;
use App\Services\Markup\Ir\Document;
use App\Services\Markup\Ir\Scope;
use Mustache\Engine as Mustache;

/**
 * `{{Tpl:名字|…}}`：取标题为「Tpl:名字」的文章，用 Mustache 代入参数，结果按 markdown 解析成下一层内容。
 */
final class TplLoader extends Loader
{
    /** @var array<string, array{title: string, params: array<string, string>, scope: Scope}> */
    private array $pending = [];

    /** @var array<string, array{content: string, document: ?Document}|null> */
    private array $results = [];

    /**
     * @param  array<string, string>  $params
     */
    public function request(string $title, array $params, Scope $scope): string
    {
        // 层数进 key：模板正文里再用同一个模板时逐层展开，到最大层数为止
        $key = $title."\x1F".json_encode($params)."\x1F".$scope->depth;
        if (! array_key_exists($key, $this->results)) {
            $this->pending[$key] = ['title' => $title, 'params' => $params, 'scope' => $scope];
        }

        return $key;
    }

    /**
     * @return array{content: string, document: ?Document}|null
     */
    public function result(string $key): ?array
    {
        return $this->results[$key] ?? null;
    }

    public function hasPending(): bool
    {
        return $this->pending !== [];
    }

    public function load(): void
    {
        $batch = $this->pending;
        $this->pending = [];
        $articles = Article::whereIn('title', array_values(array_unique(array_column($batch, 'title'))))
            ->get(['title', 'content'])
            ->keyBy('title');
        $mustache = new Mustache(['entity_flags' => ENT_QUOTES, 'escape' => fn ($value) => $value]);

        foreach ($batch as $key => $request) {
            $article = $articles->get($request['title']);
            if ($article === null) {
                $this->results[$key] = null;

                continue;
            }
            $content = (string) $article->content;
            if ($request['params'] !== []) {
                $content = $mustache->render($content, $request['params']);
            }
            $document = null;
            if ($request['scope']->canNest()) {
                $document = $this->resolver->parser->parse($content, $request['scope']->nested(contentType: $request['scope']->contentType));
                $this->resolver->collect($document);
            }
            $this->results[$key] = ['content' => $content, 'document' => $document];
        }
    }
}
