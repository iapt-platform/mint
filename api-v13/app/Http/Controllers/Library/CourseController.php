<?php

namespace App\Http\Controllers\Library;

use App\Http\Api\UserApi;
use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    // 无封面时的渐变占位色池：按课程 id 取余，保证同一课程颜色稳定
    private array $coverGradients = [
        'linear-gradient(160deg, #2d2010, rgb(150, 104, 40))',
        'linear-gradient(160deg, #1a2d10, rgb(88, 122, 44))',
        'linear-gradient(160deg, #0d1f3c, rgb(45, 92, 138))',
        'linear-gradient(160deg, #2d1020, rgb(140, 60, 96))',
        'linear-gradient(160deg, #1a1a2d, rgb(72, 70, 128))',
        'linear-gradient(160deg, #1a2820, rgb(52, 110, 90))',
    ];

    // 讲师头像文字底色池
    private array $authorColors = [
        '#c8860a',
        '#2e7d32',
        '#1565c0',
        '#6a1b9a',
        '#c62828',
        '#00695c',
        '#4e342e',
        '#37474f',
    ];

    /**
     * 课程栏目首页：Hero 由布局渲染，正文含统计条、最新课程、开放报名、历史课程预览。
     */
    public function index(): View
    {
        $now = Carbon::now();
        $base = Course::where('publicity', 30);

        // 可报名：当前处于报名窗口内（报名开始 <= 现在 <= 报名结束）
        $signUpOpen = fn ($query) => $query
            ->where('sign_up_start_at', '<=', $now)
            ->where('sign_up_end_at', '>=', $now);

        // 进行中：当前处于课程时间窗口内（课程开始 <= 现在 <= 课程结束）
        $inProgress = fn ($query) => $query
            ->where('start_at', '<=', $now)
            ->where('end_at', '>=', $now);

        // 最新课程：可报名 且 进行中（已结束的课程无论多新都不出现在这里）
        $latest = (clone $base)
            ->where($signUpOpen)
            ->where($inProgress)
            ->orderByDesc('created_at')
            ->take(4)
            ->get();

        // 开放报名：可报名
        $open = (clone $base)
            ->where($signUpOpen)
            ->orderBy('start_at')
            ->get();

        // 历史课程：已结束（课程时间已过）
        $history = (clone $base)
            ->where('end_at', '<', $now)
            ->orderByDesc('end_at')
            ->take(5)
            ->get();

        $stats = [
            'total' => (clone $base)->count(),
            'open' => (clone $base)->where($signUpOpen)->count(),
            'closed' => (clone $base)->where('end_at', '<', $now)->count(),
        ];

        return view('library.course.index', [
            'latestCourses' => $this->present($latest),
            'openCourses' => $this->present($open),
            'historyCourses' => $this->present($history),
            'stats' => $stats,
        ]);
    }

    /**
     * 历史课程列表页：分页展示已结束的公开课程。
     */
    public function history(Request $request): View
    {
        $perPage = 10;

        $paginator = Course::where('publicity', 30)
            ->where('end_at', '<', Carbon::now())
            ->orderByDesc('end_at')
            ->paginate($perPage);

        $paginator->setCollection($this->present($paginator->getCollection()));

        return view('library.course.history', [
            'courses' => $paginator,
            'total' => $paginator->total(),
        ]);
    }

    // -------------------------------------------------------------------------
    // 将 Course 集合加工为视图所需数组：批量解析讲师，避免 N+1
    // -------------------------------------------------------------------------
    private function present(Collection $courses): Collection
    {
        if ($courses->isEmpty()) {
            return collect();
        }

        // 讲师：按 teacher uuid 一次批量解析
        $teacherUids = $courses->pluck('teacher')->filter()->unique()->values();
        $teachers = $teacherUids->isEmpty()
            ? collect()
            : collect(UserApi::getListByUuid($teacherUids->all()))->keyBy('id');

        $baseUrl = rtrim(config('mint.server.workspace_base_path'), '/');

        return $courses->values()->map(function (Course $course, int $index) use ($teachers, $baseUrl) {
            $teacher = $teachers->get($course->teacher);

            return [
                'id' => $course->id,
                'title' => $course->title,
                'subtitle' => $course->subtitle,
                'summary' => $course->summary,
                'number' => (int) $course->number,
                'join' => $course->join,
                'start_at' => $course->start_at,
                'end_at' => $course->end_at,
                'sign_up_end_at' => $course->sign_up_end_at,
                'start_date' => $course->start_at ? Carbon::parse($course->start_at)->format('Y-m-d') : null,
                'sign_up_end_date' => $course->sign_up_end_at ? Carbon::parse($course->sign_up_end_at)->format('Y-m-d') : null,
                'cover_url' => $this->coverUrl($course),
                'cover_gradient' => $this->coverGradients[$this->colorIndex($course->id) % count($this->coverGradients)],
                'teacher' => $this->formatTeacher($teacher, $index),
                'status' => $this->statusOf($course),
                'detail_url' => $baseUrl.'/course/'.$course->id,
            ];
        });
    }

    private function statusOf(Course $course): string
    {
        if (! $course->sign_up_start_at || ! $course->sign_up_end_at) {
            return 'closed';
        }

        return Carbon::now()->between(
            Carbon::parse($course->sign_up_start_at),
            Carbon::parse($course->sign_up_end_at)
        ) ? 'open' : 'closed';
    }

    private function formatTeacher(?array $teacher, int $index): array
    {
        $name = $teacher['nickName'] ?? null;
        if (! $name || $name === 'unknown') {
            return ['name' => null, 'avatar' => null, 'initials' => null, 'color' => null];
        }

        return [
            'name' => $name,
            'avatar' => $teacher['avatar'] ?? null,
            'initials' => mb_substr($name, 0, 2),
            'color' => $this->authorColors[$index % count($this->authorColors)],
        ];
    }

    // -------------------------------------------------------------------------
    // 封面 URL：cover 可能是文件名（Storage），也可能是附件 uuid
    // -------------------------------------------------------------------------
    private function coverUrl(Course $course): ?string
    {
        $cover = $course->cover;
        if (! $cover) {
            return null;
        }

        // 附件 uuid：取 _m 中图
        if (Str::isUuid($cover)) {
            $attachment = Attachment::find($cover);
            if ($attachment) {
                return Storage::disk('public')->url($attachment->bucket.'/'.$attachment->id.'_m.jpg');
            }

            return null;
        }

        $thumb = str_replace('.jpg', '_m.jpg', $cover);

        return App::environment(['local', 'testing'])
            ? Storage::url($thumb)
            : Storage::temporaryUrl($thumb, now()->addDays(6));
    }

    private function colorIndex(string $id): int
    {
        return hexdec(substr(str_replace('-', '', $id), 0, 4)) % 255;
    }
}
