<?php
// app/Http/Controllers/BlogController.php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use App\Models\ProgressChapter;

use App\Http\Api\UserApi;
use Illuminate\Support\Facades\Log;

use App\Services\ProgressChapterService;

class BlogController extends Controller
{
    protected         $categories = [
        ['id' => 'sutta', 'label' => 'suttapiṭaka'],
        ['id' => 'vinaya', 'label' => 'vinayapiṭaka'],
        ['id' => 'abhidhamma', 'label' => 'abhidhammapiṭaka'],
        ['id' => 'añña', 'label' => 'añña'],
        ['id' => 'mūla', 'label' => 'mūla'],
        ['id' => 'aṭṭhakathā', 'label' => 'aṭṭhakathā'],
        ['id' => 'ṭīkā', 'label' => 'ṭīkā'],
    ];
    // 首页 - 最新博文列表
    public function index($user)
    {
        $user = UserApi::getByName($user);
        $posts = ProgressChapter::with('channel')
            ->where('progress', '>', 0.9)
            ->whereHas('channel', function ($query) use ($user) {
                $query->where('status', 30)->where('owner_uid', $user['id']);
            })
            ->latest()
            ->paginate(10);

        $categories = $this->categories;
        /*
        $posts = Post::published()
            ->with(['category', 'tags'])
            ->latest()
            ->paginate(10);

        $categories = Category::withCount('posts')->get();
        $popularPosts = Post::published()
            ->orderBy('views_count', 'desc')
            ->take(5)
            ->get();
*/
        //return view('blog.index', compact('posts', 'categories', 'popularPosts'));
        return view('blog.index', compact('user', 'posts', 'categories'));
    }

    /*
    // 博文详情页
    public function show(Post $post)
    {
        if (!$post->is_published) {
            abort(404);
        }

        $post->incrementViews();
        $post->load(['category', 'tags']);

        // 相关文章
        $relatedPosts = Post::published()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->take(3)
            ->get();

        // 上一篇和下一篇
        $prevPost = Post::published()
            ->where('published_at', '<', $post->published_at)
            ->latest()
            ->first();

        $nextPost = Post::published()
            ->where('published_at', '>', $post->published_at)
            ->oldest()
            ->first();

        return view('blog.show', compact('post', 'relatedPosts', 'prevPost', 'nextPost'));
    }

    // 分类列表
    public function categories()
    {
        $categories = Category::withCount('posts')
            ->having('posts_count', '>', 0)
            ->orderBy('posts_count', 'desc')
            ->get();

        return view('blog.categories', compact('categories'));
    }
*/
    // 分类下的文章
    public function category(
        $user,
        $category1,
        $category2 = null,
        $category3 = null,
        $category4 = null,
        $category5 = null
    ) {
        $user = UserApi::getByName($user);
        $categories = $this->categories;
        $chapterService = new ProgressChapterService();

        $tags = $this->getCategories($category1, $category2, $category3, $category4, $category5);
        $posts = $chapterService->setProgress(0.9)->setChannelOwnerId($user['id'])
            ->setTags($tags)
            ->get();
        $count = count($posts);
        $current = array_map(function ($item) {
            return ['id' => $item, 'label' => $item];
        }, $tags);

        $tagOptions = $chapterService->setProgress(0.9)->setChannelOwnerId($user['id'])
            ->setTags($tags)
            ->getTags();
        return view('blog.category', compact('user', 'categories', 'posts', 'current', 'tagOptions', 'count'));
    }

    private function getCategories($category1, $category2, $category3, $category4, $category5)
    {
        $category = [];
        if ($category1) {
            $category[] = $category1;
        }
        if ($category2) {
            $category[] = $category2;
        }
        if ($category3) {
            $category[] = $category3;
        }
        if ($category4) {
            $category[] = $category4;
        }
        if ($category5) {
            $category[] = $category5;
        }
        return $category;
    }
    /*
    // 年度归档
    public function archives()
    {
        $archives = Post::published()
            ->selectRaw('YEAR(published_at) as year, COUNT(*) as count')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->get();

        return view('blog.archives', compact('archives'));
    }

    // 指定年份的文章
    public function archivesByYear($year)
    {
        $posts = Post::published()
            ->whereYear('published_at', $year)
            ->with(['category', 'tags'])
            ->latest()
            ->paginate(15);

        // 按月分组
        $postsByMonth = $posts->getCollection()->groupBy(function ($post) {
            return $post->published_at->format('Y-m');
        });

        return view('blog.archives-year', compact('posts', 'postsByMonth', 'year'));
    }

    // 标签页面
    public function tag(Tag $tag)
    {
        $posts = $tag->posts()
            ->published()
            ->with(['category', 'tags'])
            ->latest()
            ->paginate(10);

        return view('blog.tag', compact('tag', 'posts'));
    }

    // 搜索
    public function search(Request $request)
    {
        $query = $request->get('q');

        if (empty($query)) {
            return redirect()->route('blog.index');
        }

        $posts = Post::published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('content', 'LIKE', "%{$query}%")
                    ->orWhere('excerpt', 'LIKE', "%{$query}%");
            })
            ->with(['category', 'tags'])
            ->latest()
            ->paginate(10);

        return view('blog.search', compact('posts', 'query'));
    }
        */
}
