{{-- resources/views/components/library/course-row.blade.php
     课程横向列表行（用于「开放报名」与「历史课程」列表）。
     Props: $course — 由 Library\CourseController 加工后的课程数组。
--}}
@props(['course'])

@php
    $teacher = $course['teacher'] ?? [];
    $status = $course['status'] ?? 'closed';
    $statusKey = $status === 'open' ? 'course_status_open' : 'course_status_closed';
    $cover = $course['cover_url'] ?? null;
@endphp

<a href="{{ $course['detail_url'] }}" class="course-row" target="_blank" rel="noopener">

    {{-- 缩略图 --}}
    <div class="course-row__thumb" style="{{ empty($cover) ? 'background:'.$course['cover_gradient'] : '' }}">
        @if($cover)
        <img src="{{ $cover }}" alt="{{ $course['title'] }}" loading="lazy">
        @else
        <span>{{ $course['title'] }}</span>
        @endif
    </div>

    {{-- 主体 --}}
    <div class="course-row__main">
        <h3 class="course-row__title">{{ $course['title'] }}</h3>

        @if(!empty($course['summary']))
        <div class="course-row__summary">{{ $course['summary'] }}</div>
        @endif

        <div class="course-row__meta">
            @if(!empty($teacher['name']))
            <span class="course-row__teacher">
                <x-ui.author-avatar
                    :avatar="$teacher['avatar'] ?? null"
                    :color="$teacher['color']"
                    :initials="$teacher['initials']"
                    :name="$teacher['name']"
                    size="sm" />
            </span>
            @endif

            @if($course['number'] > 0)
            <span class="course-row__period">{{ __('library.course_period', ['n' => $course['number']]) }}</span>
            @endif

            <span class="course-row__members">{{ __('library.course_members', ['n' => $course['member_count']]) }}</span>

            @if($course['start_date'])
            <span class="course-row__date">{{ $course['start_date'] }}</span>
            @endif
        </div>
    </div>

    {{-- 右侧：状态 + 报名引导 --}}
    <div class="course-row__side">
        <span class="course-badge course-badge--{{ $status }}">
            {{ __("library.{$statusKey}") }}
        </span>

        @if($status === 'open')
        <span class="course-row__cta">{{ __('library.course_signup') }} <i class="ti ti-chevron-right"></i></span>
        @endif
    </div>

</a>
