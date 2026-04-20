<?php

// app/View/Composers/BlogViewComposer.php
// 自动向所有 blog.* 视图注入右边栏数据。
// 在 AppServiceProvider 的 boot() 方法中注册：
//
//   use App\View\Composers\BlogViewComposer;
//   View::composer('blog.*', BlogViewComposer::class);
//   View::composer('layouts.blog', BlogViewComposer::class);
//
// 注意：layouts.blog 也需要注册，否则布局文件拿不到数据。

namespace App\View\Composers;

use Illuminate\View\View;

class BlogViewComposer
{
    public function compose(View $view): void
    {
        $data = $view->getData();

        // $user 由各 Controller 方法传入，Composer 不覆盖
        $user = $data['user'] ?? null;

        if (!$user) {
            return;
        }

        $userName = $user['userName'] ?? null;

        if (!$userName) {
            return;
        }

        // 避免重复查询：如果已有数据则跳过
        if ($view->offsetExists('categories') && $view->offsetExists('archives')) {
            return;
        }

        // 以下查询根据你的实际 Service/Model 替换
        // 示例结构，保持与 BlogController 现有数据结构一致

        // Categories：[['id' => ..., 'label' => ...], ...]
        if (!$view->offsetExists('categories')) {
            $view->with('categories', $this->getCategories($userName));
        }

        // Tags：[['name' => ...], ...]
        if (!$view->offsetExists('tags')) {
            $view->with('tags', $this->getTags($userName));
        }

        // Archives：[['year' => ..., 'count' => ...], ...]
        if (!$view->offsetExists('archives')) {
            $view->with('archives', $this->getArchives($userName));
        }
    }

    protected function getCategories(string $userName): array
    {
        // 替换为你的实际查询
        // 例：return BlogService::getCategories($userName);
        return [
            ['id' => 'dharma',   'label' => '佛法'],
            ['id' => 'vinaya',   'label' => '戒律'],
            ['id' => 'pali',     'label' => '巴利文'],
            ['id' => 'practice', 'label' => '禅修'],
        ];
    }

    protected function getTags(string $userName): array
    {
        // 替换为你的实际查询
        return [
            ['name' => 'Pāli'],
            ['name' => 'Vinaya'],
            ['name' => 'Sutta'],
            ['name' => 'Abhidhamma'],
            ['name' => '三藏'],
            ['name' => '注释'],
        ];
    }

    protected function getArchives(string $userName): array
    {
        // 替换为你的实际查询
        // 返回格式：[['year' => 2024, 'count' => 12], ...]
        return [['year' => 2024, 'count' => 12]];
    }
}
