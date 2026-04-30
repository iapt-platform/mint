{{-- resources/views/layouts/reader.blade.php
     全站阅读页布局。沉浸式，无 library navbar，无 footer。
     用于：tipitaka/read、anthology/read、wiki/show、blog/show。
--}}
@extends('layouts.base')

@push('styles')
    @vite(['resources/css/library.css', 'resources/css/reader.css'])
@endpush

@section('page')
    @yield('reader-content')
@endsection
