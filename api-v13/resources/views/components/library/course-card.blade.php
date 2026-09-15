{{-- resources/views/components/library/course-card.blade.php
     课程封面卡片（用于「最新课程」推荐位网格）。
     Props: $course — 由 Library\CourseController 加工后的课程数组。
--}}
@props(['course'])

@php
    $teacher = $course['teacher'] ?? [];
    $status = $course['status'] ?? 'closed';
    $statusKey = $status === 'open' ? 'course_status_open' : 'course_status_closed';
    $cover = $course['cover_url'] ?? null;
@endphp

<a href="{{ $course['detail_url'] }}" class="course-card" target="_blank" rel="noopener">

    {{-- 封面 --}}
    <div class="course-card__cover" style="{{ empty($cover) ? 'background:'.$course['cover_gradient'] : '' }}">
        @if($cover)
        <img src="{{ $cover }}" alt="{{ $course['title'] }}" loading="lazy" class="course-card__cover-img">
        @else
        <span class="course-card__cover-fallback">{{ $course['title'] }}</span>
        @endif

        <span class="course-badge course-badge--{{ $status }}">
            {{ __("library.{$statusKey}") }}
        </span>
    </div>

    {{-- 正文 --}}
    <div class="course-card__body">
        <h3 class="course-card__title">{{ $course['title'] }}</h3>

        <div class="course-card__teacher">
            @if(!empty($teacher['name']))
            <x-ui.author-avatar
                :avatar="$teacher['avatar'] ?? null"
                :color="$teacher['color']"
                :initials="$teacher['initials']"
                :name="$teacher['name']"
                size="sm" />
            @endif
        </div>

        <div class="course-card__foot">
            @if($course['number'] > 0)
            <span class="course-card__period">{{ __('library.course_period', ['n' => $course['number']]) }}</span>
            @endif
            <span class="course-card__members">{{ __('library.course_members', ['n' => $course['member_count']]) }}</span>
        </div>
    </div>

</a>
