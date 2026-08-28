{{-- resources/views/library/course/history.blade.php
     历史课程列表页（分页）。
--}}
@extends('layouts.library')

@section('title', __('library.course_history_title') . ' · ' . __('library.site_name'))

@push('styles')
@vite('resources/css/modules/library-course.css')
@endpush

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('library.home') }}">{{ __('library.home') }}</a>
</li>
<li class="breadcrumb-item">
    <a href="{{ route('library.course') }}">{{ __('library.course') }}</a>
</li>
<li class="breadcrumb-item active">{{ __('library.course_history_title') }}</li>
@endsection

@section('hero')
<div class="course-page-header">
    <div class="container-xl">
        <h1>
            {{ __('library.course_history_title') }}
            <span class="course-count-badge">{{ $total }}</span>
        </h1>
        <p>{{ __('library.course_history_subtitle') }}</p>
    </div>
</div>
@endsection

@section('content')
<div class="page-body">
    <div class="container-xl">

        <div class="lib-section">
            @if($courses->isNotEmpty())
            <div class="course-list">
                @foreach($courses as $course)
                <x-library.course-row :course="$course" />
                @endforeach
            </div>

            {{-- 分页 --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $courses->links('library.anthology.pagination') }}
            </div>
            @else
            <div class="wiki-card">
                <x-ui.empty-state :title="__('library.course_no_history')" />
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
