{{-- resources/views/library/course/index.blade.php
     课程栏目首页。
     区块：Hero → 统计条 → 最新课程 → 开放报名 → 历史课程预览
--}}
@extends('layouts.library')

@section('title', __('library.course') . ' · ' . __('library.site_name'))

@push('styles')
@vite('resources/css/modules/library-course.css')
@endpush

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('library.home') }}">{{ __('library.home') }}</a>
</li>
<li class="breadcrumb-item active">{{ __('library.course') }}</li>
@endsection

{{-- Hero --}}
@section('hero')
<section class="hero-section"
    style="background-image: url('{{ URL::asset('assets/images/hero-1.jpg') }}')">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1 class="hero-title">{{ __('library.course_hero_title') }}</h1>
        <p class="hero-subtitle">{{ __('library.course_hero_subtitle') }}</p>
        <div class="search-box">
            <x-ui.search-input
                :placeholder="__('library.course_search_placeholder')"
                size="lg" />
        </div>
    </div>
</section>
@endsection

@section('content')
<div class="page-body">
    <div class="container-xl">

        {{-- ── 统计条 ── --}}
        <div class="course-stats">
            <div class="course-stat">
                <div class="course-stat__value">{{ $stats['total'] }}</div>
                <div class="course-stat__label">{{ __('library.course_stat_total') }}</div>
            </div>
            <div class="course-stat">
                <div class="course-stat__value">{{ $stats['open'] }}</div>
                <div class="course-stat__label">{{ __('library.course_stat_open') }}</div>
            </div>
            <div class="course-stat">
                <div class="course-stat__value">{{ $stats['closed'] }}</div>
                <div class="course-stat__label">{{ __('library.course_stat_closed') }}</div>
            </div>
        </div>

        {{-- ── 一、最新课程 ── --}}
        <div class="lib-section">
            <div class="lib-section__header">
                <h2 class="lib-section__title">
                    <i class="ti ti-trending-up"></i>
                    {{ __('library.course_section_latest') }}
                </h2>
            </div>

            @if($latestCourses->isNotEmpty())
            <div class="course-grid">
                @foreach($latestCourses as $course)
                <x-library.course-card :course="$course" />
                @endforeach
            </div>
            @else
            <div class="wiki-card">
                <x-ui.empty-state :title="__('library.course_no_latest')" />
            </div>
            @endif
        </div>

        {{-- ── 二、开放报名 ── --}}
        <div class="lib-section">
            <div class="lib-section__header">
                <h2 class="lib-section__title">
                    <i class="ti ti-calendar-event"></i>
                    {{ __('library.course_section_open') }}
                </h2>
            </div>

            @if($openCourses->isNotEmpty())
            <div class="course-list">
                @foreach($openCourses as $course)
                <x-library.course-row :course="$course" />
                @endforeach
            </div>
            @else
            <div class="wiki-card">
                <x-ui.empty-state :title="__('library.course_no_open')" />
            </div>
            @endif
        </div>

        {{-- ── 三、历史课程 ── --}}
        <div class="lib-section">
            <div class="lib-section__header">
                <h2 class="lib-section__title">
                    <i class="ti ti-history"></i>
                    {{ __('library.course_section_history') }}
                </h2>
                <a href="{{ route('library.course.history') }}" class="lib-section__more">
                    {{ __('library.course_view_all') }} <i class="ti ti-arrow-right"></i>
                </a>
            </div>

            @if($historyCourses->isNotEmpty())
            <div class="course-list">
                @foreach($historyCourses as $course)
                <x-library.course-row :course="$course" />
                @endforeach
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
