<?php

namespace App\Services\Markup\Resolve;

use App\Services\Markup\Ir\Call;
use App\Services\Markup\Ir\Document;
use App\Services\Markup\Ir\Element;
use App\Services\Markup\Ir\Node;
use App\Services\Markup\Parsing\MarkupParser;
use App\Services\Markup\RenderOptions;

/**
 * 一次渲染的取数中心：遍历 IR 让每个模板登记要查的键，再按 loader 批量查。
 *
 * 一章几百个术语、几百个句子，各查一次（加上回退查询）而不是每个模板各查几次。
 */
final class Resolver
{
    /** 取数轮数上限：文章 → 句子 → 术语 note → … 每层一轮，正常两三轮就停 */
    private const MAX_ROUNDS = 10;

    /** @var array<class-string<Loader>, Loader> */
    private array $loaders = [];

    private ChannelDirectory $channels;

    public function __construct(
        public readonly RenderOptions $options,
        public readonly MarkupParser $parser,
    ) {
        $this->channels = new ChannelDirectory;
    }

    /**
     * @template T of Loader
     *
     * @param  class-string<T>  $class
     * @return T
     */
    public function loader(string $class): Loader
    {
        if (! isset($this->loaders[$class])) {
            $loader = app()->make($class);
            $loader->setResolver($this);
            $this->loaders[$class] = $loader;
        }

        /** @var T */
        return $this->loaders[$class];
    }

    public function channels(): ChannelDirectory
    {
        return $this->channels;
    }

    public function collect(Document $document): void
    {
        $this->channels->request($document->scope->channels);
        $this->collectNodes($document->nodes);
    }

    /**
     * @param  list<Node>  $nodes
     */
    private function collectNodes(array $nodes): void
    {
        foreach ($nodes as $node) {
            if ($node instanceof Element) {
                $this->collectNodes($node->children);
            } elseif ($node instanceof Call) {
                $node->template->collect($node, $this);
                foreach ($node->slots as $slot) {
                    $this->collect($slot);
                }
            }
        }
    }

    public function run(): void
    {
        for ($round = 0; $round < self::MAX_ROUNDS; $round++) {
            $pending = array_filter($this->loaders, fn (Loader $loader) => $loader->hasPending());
            if ($pending === []) {
                return;
            }
            foreach ($pending as $loader) {
                $loader->load();
            }
        }
    }
}
