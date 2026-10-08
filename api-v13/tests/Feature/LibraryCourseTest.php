<?php

use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * 造一门公开课程（publicity=30），返回模型。
 *
 * 覆盖报名窗口与课程窗口用四个字段：sign_up_start_at / sign_up_end_at
 * 决定「可报名」，start_at / end_at 决定「进行中 / 已结束」。
 */
function makeLibraryCourse(array $overrides = []): Course
{
    $course = new Course;
    $course->forceFill(array_merge([
        'id' => (string) Str::uuid(),
        'title' => '测试课程',
        'studio_id' => (string) Str::uuid(),
        'publicity' => 30,
    ], $overrides))->save();

    return $course;
}

it('partitions courses by sign-up window and course window', function () {
    $now = Carbon::now();

    makeLibraryCourse([
        'title' => '进行中可报名',
        'sign_up_start_at' => $now->copy()->subDays(10),
        'sign_up_end_at' => $now->copy()->addDays(10),
        'start_at' => $now->copy()->subDays(5),
        'end_at' => $now->copy()->addDays(5),
    ]);

    // 已结束：课程时间已过（报名也早已结束），无论多新都不进「最新课程」
    makeLibraryCourse([
        'title' => '已结束课程',
        'sign_up_start_at' => $now->copy()->subDays(40),
        'sign_up_end_at' => $now->copy()->subDays(20),
        'start_at' => $now->copy()->subDays(30),
        'end_at' => $now->copy()->subDays(1),
    ]);

    // 未开始但可报名：只进「开放报名」
    makeLibraryCourse([
        'title' => '未开始可报名',
        'sign_up_start_at' => $now->copy()->subDays(1),
        'sign_up_end_at' => $now->copy()->addDays(10),
        'start_at' => $now->copy()->addDays(5),
        'end_at' => $now->copy()->addDays(20),
    ]);

    // 进行中但报名已结束：三个区块都不该出现
    makeLibraryCourse([
        'title' => '进行中报名已结束',
        'sign_up_start_at' => $now->copy()->subDays(20),
        'sign_up_end_at' => $now->copy()->subDays(1),
        'start_at' => $now->copy()->subDays(5),
        'end_at' => $now->copy()->addDays(5),
    ]);

    $response = $this->get('/library/course')->assertOk();

    // 最新课程 = 可报名 且 进行中
    $response->assertViewHas('latestCourses', function ($courses) {
        $titles = $courses->pluck('title')->all();

        return in_array('进行中可报名', $titles, true)
            && ! in_array('已结束课程', $titles, true)
            && ! in_array('未开始可报名', $titles, true)
            && ! in_array('进行中报名已结束', $titles, true);
    });

    // 开放报名 = 可报名
    $response->assertViewHas('openCourses', function ($courses) {
        $titles = $courses->pluck('title')->all();

        return in_array('进行中可报名', $titles, true)
            && in_array('未开始可报名', $titles, true)
            && ! in_array('已结束课程', $titles, true)
            && ! in_array('进行中报名已结束', $titles, true);
    });

    // 历史课程 = 已结束
    $response->assertViewHas('historyCourses', function ($courses) {
        $titles = $courses->pluck('title')->all();

        return in_array('已结束课程', $titles, true)
            && ! in_array('进行中可报名', $titles, true)
            && ! in_array('未开始可报名', $titles, true)
            && ! in_array('进行中报名已结束', $titles, true);
    });

    // 统计条：open=可报名数量，closed=已结束数量
    $response->assertViewHas('stats', function ($stats) {
        return $stats['total'] === 4
            && $stats['open'] === 2
            && $stats['closed'] === 1;
    });
});

it('history page lists only ended courses', function () {
    $now = Carbon::now();

    makeLibraryCourse([
        'title' => '已结束课程',
        'start_at' => $now->copy()->subDays(30),
        'end_at' => $now->copy()->subDays(1),
    ]);

    makeLibraryCourse([
        'title' => '进行中可报名',
        'sign_up_start_at' => $now->copy()->subDays(10),
        'sign_up_end_at' => $now->copy()->addDays(10),
        'start_at' => $now->copy()->subDays(5),
        'end_at' => $now->copy()->addDays(5),
    ]);

    $this->get('/library/course/history')
        ->assertOk()
        ->assertViewHas('courses', function ($paginator) {
            $titles = $paginator->getCollection()->pluck('title')->all();

            return in_array('已结束课程', $titles, true)
                && ! in_array('进行中可报名', $titles, true);
        });
});

it('renders enrollment quota instead of a cohort number', function () {
    $now = Carbon::now();

    makeLibraryCourse([
        'title' => '限名额课程',
        'number' => 2000,
        'sign_up_start_at' => $now->copy()->subDays(10),
        'sign_up_end_at' => $now->copy()->addDays(10),
        'start_at' => $now->copy()->subDays(5),
        'end_at' => $now->copy()->addDays(5),
    ]);

    makeLibraryCourse([
        'title' => '不限名额课程',
        'number' => 0,
        'sign_up_start_at' => $now->copy()->subDays(10),
        'sign_up_end_at' => $now->copy()->addDays(10),
        'start_at' => $now->copy()->subDays(5),
        'end_at' => $now->copy()->addDays(5),
    ]);

    $this->get('/library/course?lang=zh-Hans')
        ->assertOk()
        ->assertSee('招生 2000 人')
        ->assertDontSee('第 2000 期')
        ->assertDontSee('招生 0 人')
        // 报名人数不再展示
        ->assertDontSee('course-card__members')
        ->assertDontSee('course-row__members');
});
