
<!DOCTYPE html>
<html lang="en" data-appearance="{&quot;appearance&quot;:&quot;system&quot;,&quot;store&quot;:1}" x-data="{ show_mobile_nav: false, theme: typhoonRetrieve().theme, appearance: typhoonRetrieve().appearance }" :class="[ show_mobile_nav ? 'overflow-hidden' : '', theme ]" class="overflow-x-hidden">
<head>
<meta charset="utf-8" />
<title>wikipali-{{ $title }}</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="description" content="{{ $description }}" />
<link rel="icon" href="/assets/images/favicon.ico" />
<link rel="canonical" href="https://www.wikipali.org/" />
<link href="/assets/typhoon/css/notices.css" type="text/css" rel="stylesheet">
<link href="/assets/typhoon/css/glightbox.min.css" type="text/css" rel="stylesheet">
<link href="/assets/typhoon/css/site.css" type="text/css" rel="stylesheet">
<link href="/assets/typhoon/css/form-styles.css" type="text/css" rel="stylesheet">
<style>
:root {
  --color-primary: #FF4C41;
  --color-primary__lighter: #ffaca7;
  --color-primary__darker: #da0d00;
}
#sign_in {
    display:none;
}
</style>
<script src="/assets/typhoon/js/alpine.js" defer></script>
</head>
<body id="top" class="flex flex-col items-stretch min-h-screen antialiased relative bg-white dark:bg-gray-900 overflow-x-hidden text-gray-600 dark:text-gray-400 " @typhoon-theme.window="theme = $event.detail.theme || ''; appearance = $event.detail.appearance || '';">
<div class="relative bg-orange-300">
<div class="max-w-screen-xl mx-auto py-3 px-3 sm:px-6 lg:px-8" style='display:none'>
notification
</div>
</div>
<div class="flex-1 flex flex-col relative">
<header class="absolute w-full z-10 pb-1 h-16 flex items-center " style>
<div class="flex-auto xl:container xl:mx-auto md:px-6 px-4">
<nav class="header-nav relative flex items-center justify-between lg:justify-start animated ">
<div class="flex items-center">
<div class="flex items-center justify-between w-full md:w-auto">
<a href="/pcd/community/list" aria-label="Logo" class="text-gray-200">
<div class="site-logo h-8" style="display:flex;">

<svg id="wikipali_banner" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 221 54.417">
  <g id="Group_12" data-name="Group 12" transform="translate(-396 -320)">
    <g id="Group_2" data-name="Group 2" transform="translate(396 320)">
      <g id="Group_1" data-name="Group 1" transform="translate(39.472 12.369)">
        <path id="Path_1" data-name="Path 1"
          d="M252.239,132.886a1.184,1.184,0,0,1-.733-.244,1.144,1.144,0,0,1-.424-.63l-3.447-12.729a.825.825,0,0,1-.026-.206.683.683,0,0,1,.155-.411.655.655,0,0,1,.539-.257h1.234a1.129,1.129,0,0,1,.721.244,1.171,1.171,0,0,1,.411.63l1.7,6.97q.026.1.8,4.063a.046.046,0,0,0,.053.051.046.046,0,0,0,.051-.051q.925-3.96.952-4.063l1.8-6.97a1.187,1.187,0,0,1,1.132-.874h1a1.187,1.187,0,0,1,1.13.874l1.851,6.97q.153.643.475,1.993t.5,2.071a.046.046,0,0,0,.051.051.084.084,0,0,0,.078-.051q.076-.464.36-1.865t.462-2.2l1.647-6.97a1.187,1.187,0,0,1,1.132-.874h1.028a.66.66,0,0,1,.54.257.723.723,0,0,1,.155.437.813.813,0,0,1-.026.18l-3.266,12.729a1.143,1.143,0,0,1-.424.63,1.174,1.174,0,0,1-.733.244h-1.8a1.129,1.129,0,0,1-.721-.244,1.165,1.165,0,0,1-.411-.63l-1.62-6.3q-.258-1.028-.9-4.114a.1.1,0,0,0-.091-.051.091.091,0,0,0-.089.051q-.514,2.726-.9,4.142l-1.543,6.275a1.166,1.166,0,0,1-.411.63,1.12,1.12,0,0,1-.721.244h-1.671Z"
          transform="translate(-247.61 -111.903)" fill="#fff" />
        <path id="Path_2" data-name="Path 2"
          d="M395.183,81.944a1.988,1.988,0,0,1-1.389.5,1.942,1.942,0,0,1-1.376-.5,1.661,1.661,0,0,1-.539-1.274,1.692,1.692,0,0,1,.539-1.3,1.94,1.94,0,0,1,1.376-.5,1.978,1.978,0,0,1,1.389.5,1.673,1.673,0,0,1,.553,1.3A1.644,1.644,0,0,1,395.183,81.944ZM393.216,99.65a.92.92,0,0,1-.926-.926V86.072a.864.864,0,0,1,.269-.63.892.892,0,0,1,.657-.269h1.157a.9.9,0,0,1,.657.269.865.865,0,0,1,.271.63V98.723a.923.923,0,0,1-.928.926Z"
          transform="translate(-368.881 -78.666)" fill="#fff" />
        <path id="Path_3" data-name="Path 3"
          d="M444.3,98.574a.92.92,0,0,1-.926-.926V78.489a.869.869,0,0,1,.269-.63.892.892,0,0,1,.657-.269H445.4a.9.9,0,0,1,.657.269.863.863,0,0,1,.269.63v12.6c0,.018.013.026.038.026a.091.091,0,0,0,.065-.026l5.092-6.3a1.831,1.831,0,0,1,1.492-.693h1.518a.387.387,0,0,1,.373.244.382.382,0,0,1-.064.45l-4.269,5.092a.167.167,0,0,0,0,.18l4.937,7.74a.49.49,0,0,1,.078.257.481.481,0,0,1-.078.257.448.448,0,0,1-.437.257h-1.465a1.55,1.55,0,0,1-1.389-.772l-3.4-5.683c-.033-.069-.077-.077-.129-.026l-2.288,2.649a.336.336,0,0,0-.078.206v2.7a.92.92,0,0,1-.926.926Z"
          transform="translate(-412.163 -77.59)" fill="#fff" />
        <path id="Path_4" data-name="Path 4"
          d="M540.613,81.944a1.987,1.987,0,0,1-1.388.5,1.94,1.94,0,0,1-1.376-.5,1.661,1.661,0,0,1-.539-1.274,1.692,1.692,0,0,1,.539-1.3,1.942,1.942,0,0,1,1.376-.5,1.977,1.977,0,0,1,1.388.5,1.673,1.673,0,0,1,.553,1.3A1.649,1.649,0,0,1,540.613,81.944ZM538.646,99.65a.923.923,0,0,1-.928-.926V86.072a.86.86,0,0,1,.271-.63.892.892,0,0,1,.657-.269H539.8a.9.9,0,0,1,.657.269.863.863,0,0,1,.269.63V98.723a.92.92,0,0,1-.926.926Z"
          transform="translate(-491.128 -78.666)" fill="#fff" />
        <path id="Path_5" data-name="Path 5"
          d="M589.735,137.187a.92.92,0,0,1-.925-.925V117.746a.92.92,0,0,1,.925-.926h.642a1.016,1.016,0,0,1,.682.257,1.136,1.136,0,0,1,.373.642l.077.642c.018.035.038.053.064.053a.1.1,0,0,0,.065-.026,7.051,7.051,0,0,1,4.424-1.929,5,5,0,0,1,4.231,1.993,8.742,8.742,0,0,1,1.5,5.388,10.164,10.164,0,0,1-.515,3.3,6.991,6.991,0,0,1-1.389,2.469,6.473,6.473,0,0,1-1.993,1.5,5.339,5.339,0,0,1-2.353.54,5.846,5.846,0,0,1-3.729-1.569.031.031,0,0,0-.051,0,.075.075,0,0,0-.025.053l.076,2.366v3.754a.92.92,0,0,1-.926.925h-1.159Zm5.246-8.023a3.153,3.153,0,0,0,2.65-1.4,6.426,6.426,0,0,0,1.028-3.871q0-4.912-3.394-4.912a5.135,5.135,0,0,0-3.368,1.7.245.245,0,0,0-.078.18v6.866a.243.243,0,0,0,.078.18A4.734,4.734,0,0,0,594.981,129.164Z"
          transform="translate(-534.418 -110.264)" fill="#fff" />
        <path id="Path_6" data-name="Path 6"
          d="M691.509,105.4a4.264,4.264,0,0,1-3.073-1.143,4.27,4.27,0,0,1,.85-6.609,16.4,16.4,0,0,1,6.518-1.787c.069,0,.1-.041.1-.129q-.1-3.032-2.751-3.034a7.157,7.157,0,0,0-3.419,1,.864.864,0,0,1-.669.089.811.811,0,0,1-.539-.424l-.257-.462a.955.955,0,0,1-.089-.706.822.822,0,0,1,.424-.553,10.423,10.423,0,0,1,5.066-1.44,4.826,4.826,0,0,1,3.96,1.594,6.988,6.988,0,0,1,1.312,4.551v7.792a.923.923,0,0,1-.928.926h-.642a1.008,1.008,0,0,1-.681-.257,1.118,1.118,0,0,1-.373-.642l-.1-.721c-.018-.033-.038-.053-.065-.053s-.046.018-.064.053A7.155,7.155,0,0,1,691.509,105.4Zm-.977-18.027a.92.92,0,0,1-.926-.926v-.206a.92.92,0,0,1,.926-.926h6.3a.92.92,0,0,1,.925.926v.206a.92.92,0,0,1-.925.926Zm1.9,15.637a5.287,5.287,0,0,0,3.4-1.6.278.278,0,0,0,.077-.206V97.9c0-.086-.033-.12-.1-.1a11.688,11.688,0,0,0-4.346,1.17,2.336,2.336,0,0,0-1.286,2.045,1.82,1.82,0,0,0,.617,1.518A2.582,2.582,0,0,0,692.435,103.015Z"
          transform="translate(-617.157 -84.088)" fill="#fff" />
        <path id="Path_7" data-name="Path 7"
          d="M792.08,98.907a2.68,2.68,0,0,1-2.3-.952,4.607,4.607,0,0,1-.708-2.779V78.489a.865.865,0,0,1,.271-.63A.893.893,0,0,1,790,77.59h1.157a.9.9,0,0,1,.657.269.865.865,0,0,1,.271.63V95.333a1.12,1.12,0,0,0,.411,1.028c.034.018.11.061.231.129s.206.12.257.155.12.081.206.14a.691.691,0,0,1,.193.193.438.438,0,0,1,.064.231l.1.566a.736.736,0,0,1,.026.18.96.96,0,0,1-.155.54.792.792,0,0,1-.591.386C792.585,98.9,792.336,98.907,792.08,98.907Z"
          transform="translate(-702.754 -77.59)" fill="#fff" />
        <path id="Path_8" data-name="Path 8"
          d="M840.663,81.944a1.988,1.988,0,0,1-1.389.5,1.94,1.94,0,0,1-1.376-.5,1.661,1.661,0,0,1-.539-1.274,1.692,1.692,0,0,1,.539-1.3,1.939,1.939,0,0,1,1.376-.5,1.978,1.978,0,0,1,1.389.5,1.673,1.673,0,0,1,.553,1.3A1.649,1.649,0,0,1,840.663,81.944ZM838.7,99.65a.92.92,0,0,1-.926-.926V86.072a.864.864,0,0,1,.269-.63.892.892,0,0,1,.657-.269h1.157a.9.9,0,0,1,.657.269.865.865,0,0,1,.271.63V98.723a.923.923,0,0,1-.928.926Z"
          transform="translate(-743.346 -78.666)" fill="#fff" />
      </g>
      <path id="Path_9" data-name="Path 9"
        d="M125.632,125.382a1.531,1.531,0,0,1-1.532-1.532v-5.532c0-8.629,4.134-13.579,11.342-13.579a1.532,1.532,0,0,1,0,3.064c-5.493,0-8.28,3.537-8.28,10.517v5.532A1.529,1.529,0,0,1,125.632,125.382Z"
        transform="translate(-104.317 -88.043)" fill="#f1ca23" />
      <path id="Path_10" data-name="Path 10"
        d="M144.722,179.085a1.532,1.532,0,1,1,0-3.064c2.987,0,5.076-4,5.076-9.727V149.842a1.532,1.532,0,0,1,3.064,0v16.452C152.86,175.13,148.773,179.085,144.722,179.085Z"
        transform="translate(-120.364 -124.667)" fill="#f1ca23" />
      <path id="Path_11" data-name="Path 11"
        d="M84.262,37.339a1.531,1.531,0,0,1-1.532-1.532V1.532a1.532,1.532,0,0,1,3.064,0V35.809A1.531,1.531,0,0,1,84.262,37.339Z"
        transform="translate(-69.542)" fill="#f1ca23" />
      <path id="Path_12" data-name="Path 12"
        d="M42.892,37.339a1.531,1.531,0,0,1-1.532-1.532V1.532a1.532,1.532,0,0,1,3.064,0V35.809A1.531,1.531,0,0,1,42.892,37.339Z"
        transform="translate(-34.767)" fill="#f1ca23" />
      <path id="Path_13" data-name="Path 13"
        d="M1.532,37.339A1.531,1.531,0,0,1,0,35.808V1.532a1.532,1.532,0,0,1,3.064,0V35.809A1.533,1.533,0,0,1,1.532,37.339Z"
        fill="#f1ca23" />
    </g>

  </g>
</svg>

<span  id='nickname'></span>
</div>
</a>
</div>
</div>
<div class="hidden h-full md:flex md:flex-grow justify-end">
<ul class="flex h-16 mr-8">
@foreach ($nav as $item)
<li id="{{ $item['id'] }}" class="flex ml-4 text-sm relative inline-flex items-center pt-1 border-b-2 font-medium leading-5 transition duration-150 ease-in-out  border-transparent text-gray-400 hover:text-primary hover:border-primary focus:outline-none focus:text-primary focus:border-gray-300  ">
<div class="flex w-full h-full">
<a class="w-full flex items-center h-full px-3" href="{{ $item['link'] }}">{{ $item['title'] }}</a>
</div>
</li>
@endforeach

<li class="flex ml-4 text-sm relative inline-flex items-center pt-1 border-b-2 font-medium leading-5 transition duration-150 ease-in-out  border-transparent text-gray-400 hover:text-primary hover:border-primary focus:outline-none focus:text-primary focus:border-gray-300  ">
<div class="flex w-full h-full">
<a class="w-full flex items-center h-full px-3" href="#contact">联络我们</a>
</div>
</li>
<li class="flex ml-4 text-sm relative inline-flex items-center pt-1 border-b-2 font-medium leading-5 transition duration-150 ease-in-out  border-transparent text-gray-400 hover:text-primary hover:border-primary focus:outline-none focus:text-primary focus:border-gray-300  ">
<div class="flex w-full h-full">
<span class="w-full flex items-center h-full px-3" id='nickname'> </span>
</div>
</li>

</ul>
</div>
</nav>
</div>
<div class="flex items-center md:hidden justify-end">
<button @click="show_mobile_nav = true" aria-label="Mobile menu" type="button" class="text-gray-200 inline-flex items-center justify-center p-2 mr-2 rounded-md focus:outline-none transition duration-150 ease-in-out">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-menu inline-block current-color h-8 w-8" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none" />
<line x1="4" y1="8" x2="20" y2="8" />
<line x1="4" y1="16" x2="20" y2="16" />
</svg>
</button>
</div>
</header>
<section id="hero" class="relative  overflow-hidden ">
<img class="background-image absolute inset-0 object-cover h-full w-full" alt="Hero Image" src="{{ URL::asset('assets/images/hero.jpg') }}" />
<div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: linear-gradient(to bottom, rgba(34,34,34,0.9), rgba(34,34,34,0.4));"></div>
<div class="xl:container xl:mx-auto md:px-6 px-4 relative pt-32 md:pt-40 lg:pt-48 xl:pt-56 pb-16 md:pb-20 lg:pb-24 xl:pb-32">
<div class="flex text-center justify-center">
<div class="w-5/6 md:w-3/4 lg:w-2/3 xl:w-1/2">
<h1 class="mt-1 tracking-tight leading-tighter text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-gray-100">
{{ $title }}
</h1>
<div class="mt-3 text-gray-200 text-lg md:text-xl">
<p>{{ $subtitle }}</p>
</div>
<div class="mt-5 mt-8 flex space-x-4 justify-center">
<div class="rounded-md shadow" style="display:none;">
<a href="https://learn.getgrav.org" class="bg-primary hover:bg-gray-800 text-white w-full flex items-center justify-center px-8 py-3 border border-transparent text-base leading-6 font-medium rounded-md focus:outline-none focus:ring transition duration-300 ease-in-out md:py-4 md:text-lg md:px-10">
Read the documentation
</a>
</div>
</div>
</div>
</div>
</div>
</section>
<section class="flex-1">
<div class="pt-0">
<div id="highlights"></div>
<div class="bg-white dark:bg-gray-900 py-8 md:py-24">
<div class="xl:container xl:mx-auto md:px-6 px-4">
<div class="w-5/6 md:w-3/4 lg:w-2/3 xl:w-1/2 text-center mx-auto mb-16">
<div class="text-xs md:text-sm opacity-75 font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
让学习圣典变得更容易
</div>
<h2 class="mt-1 tracking-tight leading-tighter text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-gray-900 dark:text-gray-100">
我们的愿景
</h2>
<div class="mt-3 text-base md:text-lg lg:text-xl prose">
<p>打造一个公共的巴利圣典学习平台。</p>
</div>
</div>
<div class="grid md:grid-cols-2 xl:grid-cols-3 gap-y-8 gap-x-8 ">
<div class="flex duration-300 hover:scale-105">
<div class="flex-shrink-0">
<div class="bg-primary text-gray-200 rounded-md p-3">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-gauge inline-block w-8 h-8 stroke-current" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none" />
<circle cx="12" cy="12" r="9" />
<circle cx="12" cy="12" r="1" />
<line x1="13.41" y1="10.59" x2="16" y2="8" />
<path d="M7 12a5 5 0 0 1 5 -5" />
</svg>
</div>
</div>

<div class="ml-4">
<a class="text-primary hover:text-primary-darker dark:hover:text-primary-lighter" href="https://getgrav.org"></a>
<h3 class="font-bold mb-2 text-lg">
翻译一套三藏
</h3>

<div class="prose">
我们希望把完整的巴利三藏、义注、复注、nissaya都翻译成为中文。
</div>
</div>
</div>
<div class="flex duration-300 hover:scale-105">
<div class="flex-shrink-0">
<div class="bg-primary text-gray-200 rounded-md p-3">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-forklift inline-block w-8 h-8 stroke-current" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none" />
<circle cx="5" cy="17" r="2" />
<circle cx="14" cy="17" r="2" />
<line x1="7" y1="17" x2="12" y2="17" />
<path d="M3 17v-6h13v6" />
<path d="M5 11v-4h4" />
<path d="M9 11v-6h4l3 6" />
<path d="M22 15h-3v-10" />
<line x1="16" y1="13" x2="19" y2="13" />
</svg>
</div>
</div>

<div class="ml-4">
<a class="text-primary hover:text-primary-darker dark:hover:text-primary-lighter" href="https://learn.getgrav.org"></a>
<h3 class="font-bold mb-2 text-lg">
整理一本词典
</h3>
<div class="prose">
我们希望借助沉淀下来的数据，整理一套完整的巴中字典。这项工作将在整个三藏翻译的过程中逐渐完成。
</div>
</div>
</div>
<div class="flex duration-300 hover:scale-105">
<div class="flex-shrink-0">
<div class="bg-primary text-gray-200 rounded-md p-3">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-box inline-block w-8 h-8 stroke-current" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none" />
<polyline points="12 3 20 7.5 20 16.5 12 21 4 16.5 4 7.5 12 3" />
<line x1="12" y1="12" x2="20" y2="7.5" />
<line x1="12" y1="12" x2="12" y2="21" />
<line x1="12" y1="12" x2="4" y2="7.5" />
</svg>
</div>
</div>
<div class="ml-4">
<h3 class="font-bold mb-2 text-lg">
开发一个平台
</h3>
<div class="prose">
我们会持续开发和维护wikipali平台，并不断发展新的功能，令其越来越方便与巴利翻译和研究。
</div>
</div>
</div>
</div>
</div>
</div>


<div id="columns"></div>
<div class="bg-gray-100 dark:bg-gray-800 py-8 md:py-24">
<div class="xl:container xl:mx-auto md:px-6 px-4">
<div class>
<div class="w-5/6 md:w-3/4 lg:w-2/3 xl:w-1/2 text-center mx-auto mb-16">
<div class="text-xs md:text-sm opacity-75 font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
千年译经路
</div>
<h2 class="mt-1 tracking-tight leading-tighter text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-gray-900 dark:text-gray-100">
巴利三藏翻译工程
</h2>
</div>
<div class="mt-3 prose col-count-1 sm:col-count-2 sm:gap-x-6 lg:col-count-3 lg:gap-x-10 xl:col-count-4 xl:gap-x-12">
<p>上座部佛教相信，佛陀讲经说法时所使用的语言是当时中印度马嘎塔国(Magadha,摩揭陀国)一带的民众方言——马嘎塔口语。这种语言在西元前3世纪的阿首咖王(Asoka,阿育王)时代即随着到斯里兰卡传播佛教的马兴德阿拉汉而传到斯里兰卡，并一直流传到今天。</p>
<p>由于新哈勒人原先就有了自己的语言，当以马嘎塔语为媒介语的上座部佛教传播到斯里兰卡之后，这种语言就只是作为传诵三藏圣典之用，因此马嘎塔语又被称为「巴利语」。</p>
<p>义注起源于佛陀在世时弟子们对佛陀教导的解释，如收录于《中部》的《法嗣经》《谛分别经》《应习不应习经》等，即是沙利补答尊者详细解释佛陀简短开示的经典。佛陀入灭后，诸圣者、大长老们继续对三藏圣典进行注解诠释，这些注释文献即是上座部佛教的「义注」，它们是上座部佛教历代长老大德们传承佛陀教法的禅修精要和智慧结晶，也是对巴利语三藏圣典最为权威的解释。</p>
<p>之后的古代的大长老们又用巴利语撰写了解释义注和根本的再注释书——复注。这些古代文献已经有上千年的历史。</p>
<p>根据上座部佛教传统，巴利语三藏包含了根本，义注，复注在内的100多本书。共计800万单词。长期以来，这些文献以巴利语刻写在棕榈叶上在东南亚国家流传。</p>
<p>将这些古代文献翻译为汉语，是众多佛教徒的心愿。</p>
<p><a href="/pcd/community/list" class="font-bold">已经翻译的经文</a></p>
</div>
</div>
</div>
</div>


<div id="callout"></div>
<div class="bg-white dark:bg-gray-900 py-8 md:py-24">
<div class="xl:container xl:mx-auto md:px-6 px-4">
<div class="w-5/6 md:w-3/4 lg:w-2/3 xl:w-1/2 text-center mx-auto mb-16">
<div class="text-xs md:text-sm opacity-75 font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
翻译中心
</div>
<h2 class="mt-1 tracking-tight leading-tighter text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-gray-900 dark:text-gray-100">
wikipali（云台）翻译中心
</h2>
</div>
<div class="flex md:flex-row-reverse justify-center md:justify-start flex-wrap md:flex-no-wrap">
<div class="w-full md:w-1/2 md:pl-8 ">
<img class="mb-8 md:mb-0 rounded-md" alt="/assets/gallery/03.jpg" src="/assets/gallery/02.jpg">
</div>
<div class="w-full md:w-1/2 md:pr-8 md:pr-8 mb-4 md:mb-0">
<div class="md:text-lg prose">
<p>wikipali（云台）翻译中心坐落于云南省昆明市宜良县，致力于整理三藏和佛教文献的翻译和研究。</p>
<p>佛教文化是中国传统文化的一部分，也是全世界人类的宝贵文化遗产。然而， 由于很多佛教文献是由巴利语、梵语、缅语、泰文等记载，所以需要培养专门的小语种语言人才，进行相关的研究。</p>
<p>我们的团队由一群热爱佛教文化的志愿者组成，来自不同的国家和地区，拥有不同的背景和经验。大家互相配合，致力于将佛教文献翻译成汉语，让更多人能够阅读和理解这些珍贵的文献</p>
<p>我们的翻译工作主要集中在三藏经典，将传统的文献转换成为通俗的现代汉语。除此之外，我们也参与翻译佛教论著、传记、诗歌等多种形式的佛教文献，保留和研究其文学、历史价值。</p>
<p>我们的翻译成果将会在wikipali网站上发布，供大家免费使用和阅读。感谢您对wikipali（云台）翻译中心的支持和关注！</p>
<p style="display:none;"><a href="https://getgrav.org" class="btn mt-4 w-content block">Find out more...</a></p>
</div>
</div>
</div>
</div>
</div>


<div id="gallery"></div>
<div class="bg-white dark:bg-gray-900 py-8 md:py-24">
<div class="xl:container xl:mx-auto md:px-6 px-4">
<div class="w-5/6 md:w-3/4 lg:w-2/3 xl:w-1/2 text-center mx-auto mb-16">
<div class="text-xs md:text-sm opacity-75 font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
欢迎加入翻译中心
</div>
<h2 class="mt-1 tracking-tight leading-tighter text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-gray-900 dark:text-gray-100">
翻译中心相册
</h2>
<div class="mt-3 text-base md:text-lg lg:text-xl prose">
<p></p>
</div>
</div>
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-y-2 gap-x-2">

@foreach ($gallery as $item)
<div class="inline-flex overflow-hidden rounded-md group safari-corner-overflow-fix">
<a href="{{ $item['image'] }}" class="glightbox inline-block" data-gallery="161b91e52e3b1158306a38186e6f5107" data-title="{{ $item['title'] }}" data-description=".{{ $item['id'] }}" data-type="image">
<img class="duration-200 group-hover:scale-110 group-hover:filter group-hover:brightness-110" title="Climbing Hilly Peaks" alt="Climbing Hilly Peaks" src="{{ $item['image'] }}" />
</a>
</div>
@endforeach


<div class="hidden">
@foreach ($gallery as $item)
    <div class="glightbox-desc {{ $item['id'] }}">
        <div class="prose">
            <p>{{ $item['description'] }}</p>
        </div>
    </div>
@endforeach

</div>
</div>
</div>
</div>
<div id="features"></div>
<div class="bg-white dark:bg-gray-900 py-8 md:py-24">
<div class="xl:container xl:mx-auto md:px-6 px-4">
<div class="w-5/6 md:w-3/4 lg:w-2/3 xl:w-1/2 text-center mx-auto mb-16">
<div class="text-xs md:text-sm opacity-75 font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
在线翻译|协作|巴利语学习
</div>
<h2 class="mt-1 tracking-tight leading-tighter text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-gray-900 dark:text-gray-100">
专门开发的巴利语学习和翻译工具
</h2>
<div class="mt-3 text-base md:text-lg lg:text-xl prose">
<p>结合多年的教学与翻译经验。专门为巴利语教学和翻译定制的多种在线编辑工具。无需安装软件。支持各种桌面操作系统。</p>
</div>
</div>
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-y-8 gap-x-8 ">

<div class="flex duration-300 hover:scale-105">
<div class="flex flex-col w-full rounded-md group hover:bg-gray-100 dark:hover:bg-gray-800">
<div class="text-gray-500 group-hover:text-primary rounded-md p-3 text-center">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-pencil inline-block w-16 h-16 stroke-current stroke-3/2 mx-auto" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none" />
<path d="M4 20h4l10.5 -10.5a1.5 1.5 0 0 0 -4 -4l-10.5 10.5v4" />
<line x1="13.5" y1="6.5" x2="17.5" y2="10.5" />
</svg>
</div>
<h3 class="mb-2 text-base md:text-lg group-hover:text-primary text-center">
在线翻译
</h3>
<div class="prose text-center">
</div>
</div>
</div>

<div class="flex duration-300 hover:scale-105">
<div class="flex flex-col w-full rounded-md group hover:bg-gray-100 dark:hover:bg-gray-800">
<div class="text-gray-500 group-hover:text-primary rounded-md p-3 text-center">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-template inline-block w-16 h-16 stroke-current stroke-3/2 mx-auto" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none" />
<rect x="4" y="4" width="16" height="4" rx="1" />
<rect x="4" y="12" width="6" height="8" rx="1" />
<line x1="14" y1="12" x2="20" y2="12" />
<line x1="14" y1="16" x2="20" y2="16" />
<line x1="14" y1="20" x2="20" y2="20" />
</svg>
</div>
<h3 class="mb-2 text-base md:text-lg group-hover:text-primary text-center">
术语模版
</h3>
<div class="prose text-center">
</div>
</div>
</div>

<div class="flex duration-300 hover:scale-105">
<div class="flex flex-col w-full rounded-md group hover:bg-gray-100 dark:hover:bg-gray-800">
<div class="text-gray-500 group-hover:text-primary rounded-md p-3 text-center">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-bolt inline-block w-16 h-16 stroke-current stroke-3/2 mx-auto" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none" />
<polyline points="13 3 13 10 19 10 11 21 11 14 5 14 13 3" />
</svg>
</div>
<h3 class="mb-2 text-base md:text-lg group-hover:text-primary text-center">
实时发布译文
</h3>
<div class="prose text-center">
</div>
</div>
</div>

<div class="flex duration-300 hover:scale-105">
<div class="flex flex-col w-full rounded-md group hover:bg-gray-100 dark:hover:bg-gray-800">
<div class="text-gray-500 group-hover:text-primary rounded-md p-3 text-center">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-tag inline-block w-16 h-16 stroke-current stroke-3/2 mx-auto" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none" />
<circle cx="8.5" cy="8.5" r="1" fill="currentColor" />
<path d="M4 7v3.859c0 .537 .213 1.052 .593 1.432l8.116 8.116a2.025 2.025 0 0 0 2.864 0l4.834 -4.834a2.025 2.025 0 0 0 0 -2.864l-8.117 -8.116a2.025 2.025 0 0 0 -1.431 -.593h-3.859a3 3 0 0 0 -3 3z" />
</svg>
</div>
<h3 class="mb-2 text-base md:text-lg group-hover:text-primary text-center">
在线讨论校对
</h3>
<div class="prose text-center">
</div>
</div>
</div>

<div class="flex duration-300 hover:scale-105">
<div class="flex flex-col w-full rounded-md group hover:bg-gray-100 dark:hover:bg-gray-800">
<div class="text-gray-500 group-hover:text-primary rounded-md p-3 text-center">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-cloud-download inline-block w-16 h-16 stroke-current stroke-3/2 mx-auto" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none" />
<path d="M19 18a3.5 3.5 0 0 0 0 -7h-1a5 4.5 0 0 0 -11 -2a4.6 4.4 0 0 0 -2.1 8.4" />
<line x1="12" y1="13" x2="12" y2="22" />
<polyline points="9 19 12 22 15 19" />
</svg>
</div>
<h3 class="mb-2 text-base md:text-lg group-hover:text-primary text-center">
译文导出
</h3>
<div class="prose text-center">
</div>
</div>
</div>

<div class="flex duration-300 hover:scale-105"  style="display:none;">
<div class="flex flex-col w-full rounded-md group hover:bg-gray-100 dark:hover:bg-gray-800">
<div class="text-gray-500 group-hover:text-primary rounded-md p-3 text-center">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-apps inline-block w-16 h-16 stroke-current stroke-3/2 mx-auto" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none" />
<rect x="4" y="4" width="6" height="6" rx="1" />
<rect x="4" y="14" width="6" height="6" rx="1" />
<rect x="14" y="14" width="6" height="6" rx="1" />
<line x1="14" y1="7" x2="20" y2="7" />
<line x1="17" y1="4" x2="17" y2="10" />
</svg>
</div>
<h3 class="mb-2 text-base md:text-lg group-hover:text-primary text-center">
Powerful Plugins
</h3>
<div class="prose text-center">
</div>
</div>
</div>

<div class="flex duration-300 hover:scale-105"  style="display:none;">
<div class="flex flex-col w-full rounded-md group hover:bg-gray-100 dark:hover:bg-gray-800">
<div class="text-gray-500 group-hover:text-primary rounded-md p-3 text-center">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-desktop inline-block w-16 h-16 stroke-current stroke-3/2 mx-auto" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none" />
<rect x="3" y="4" width="18" height="12" rx="1" />
<line x1="7" y1="20" x2="17" y2="20" />
<line x1="9" y1="16" x2="9" y2="20" />
<line x1="15" y1="16" x2="15" y2="20" />
</svg>
</div>
<h3 class="mb-2 text-base md:text-lg group-hover:text-primary text-center">
Intuitive UI
</h3>
<div class="prose text-center">
</div>
</div>
</div>

<div class="flex duration-300 hover:scale-105">
<div class="flex flex-col w-full rounded-md group hover:bg-gray-100 dark:hover:bg-gray-800">
<div class="text-gray-500 group-hover:text-primary rounded-md p-3 text-center">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-text inline-block w-16 h-16 stroke-current stroke-3/2 mx-auto" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none" />
<path d="M14 3v4a1 1 0 0 0 1 1h4" />
<path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
<line x1="9" y1="9" x2="10" y2="9" />
<line x1="9" y1="13" x2="15" y2="13" />
<line x1="9" y1="17" x2="15" y2="17" />
</svg>
</div>
<h3 class="mb-2 text-base md:text-lg group-hover:text-primary text-center">
社区字典
</h3>
<div class="prose text-center">
</div>
</div>
</div>

<div class="flex duration-300 hover:scale-105">
<div class="flex flex-col w-full rounded-md group hover:bg-gray-100 dark:hover:bg-gray-800">
<div class="text-gray-500 group-hover:text-primary rounded-md p-3 text-center">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-language inline-block w-16 h-16 stroke-current stroke-3/2 mx-auto" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none" />
<path d="M4 5h7" />
<path d="M9 3v2c0 4.418 -2.239 8 -5 8" />
<path d="M5 9c-.003 2.144 2.952 3.908 6.7 4" />
<path d="M12 20l4 -9l4 9" />
<path d="M19.1 18h-6.2" />
</svg>
</div>
<h3 class="mb-2 text-base md:text-lg group-hover:text-primary text-center">
多语言译文对照
</h3>
<div class="prose text-center">
</div>
</div>
</div>

<div class="flex duration-300 hover:scale-105"  style="display:none;">
<div class="flex flex-col w-full rounded-md group hover:bg-gray-100 dark:hover:bg-gray-800">
<div class="text-gray-500 group-hover:text-primary rounded-md p-3 text-center">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-users inline-block w-16 h-16 stroke-current stroke-3/2 mx-auto" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none" />
<circle cx="9" cy="7" r="4" />
<path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
<path d="M16 3.13a4 4 0 0 1 0 7.75" />
<path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
</svg>
</div>
<h3 class="mb-2 text-base md:text-lg group-hover:text-primary text-center">
多人协作
</h3>
<div class="prose text-center">
</div>
</div>
</div>

<div class="flex duration-300 hover:scale-105"  style="display:none;">
<div class="flex flex-col w-full rounded-md group hover:bg-gray-100 dark:hover:bg-gray-800">
<div class="text-gray-500 group-hover:text-primary rounded-md p-3 text-center">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-photo inline-block w-16 h-16 stroke-current stroke-3/2 mx-auto" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none" />
<line x1="15" y1="8" x2="15.01" y2="8" />
<rect x="4" y="4" width="16" height="16" rx="3" />
<path d="M4 15l4 -4a3 5 0 0 1 3 0l5 5" />
<path d="M14 14l1 -1a3 5 0 0 1 3 0l2 2" />
</svg>
</div>
<h3 class="mb-2 text-base md:text-lg group-hover:text-primary text-center">
Image Processing
</h3>
<div class="prose text-center">
</div>
</div>
</div>

<div class="flex duration-300 hover:scale-105">
<div class="flex flex-col w-full rounded-md group hover:bg-gray-100 dark:hover:bg-gray-800">
<div class="text-gray-500 group-hover:text-primary rounded-md p-3 text-center">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-notebook inline-block w-16 h-16 stroke-current stroke-3/2 mx-auto" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none" />
<path d="M6 4h11a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-11a1 1 0 0 1 -1 -1v-14a1 1 0 0 1 1 -1m3 0v18" />
<line x1="13" y1="8" x2="15" y2="8" />
<line x1="13" y1="12" x2="15" y2="12" />
</svg>
</div>
<h3 class="mb-2 text-base md:text-lg group-hover:text-primary text-center">
文集
</h3>
<div class="prose text-center">
</div>
</div>
</div>

<div class="flex duration-300 hover:scale-105"  style="display:none;">
<div class="flex flex-col w-full rounded-md group hover:bg-gray-100 dark:hover:bg-gray-800">
<div class="text-gray-500 group-hover:text-primary rounded-md p-3 text-center">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-github inline-block w-16 h-16 stroke-current stroke-3/2 mx-auto" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none" />
<path d="M9 19c-4.3 1.4 -4.3 -2.5 -6 -3m12 5v-3.5c0 -1 .1 -1.4 -.5 -2c2.8 -.3 5.5 -1.4 5.5 -6a4.6 4.6 0 0 0 -1.3 -3.2a4.2 4.2 0 0 0 -.1 -3.2s-1.1 -.3 -3.5 1.3a12.3 12.3 0 0 0 -6.2 0c-2.4 -1.6 -3.5 -1.3 -3.5 -1.3a4.2 4.2 0 0 0 -.1 3.2a4.6 4.6 0 0 0 -1.3 3.2c0 4.6 2.7 5.7 5.5 6c-.6 .6 -.6 1.2 -.5 2v3.5" />
</svg>
</div>
<h3 class="mb-2 text-base md:text-lg group-hover:text-primary text-center">
On Github
</h3>
<div class="prose text-center">
</div>
</div>
</div>

<div class="flex duration-300 hover:scale-105">
<div class="flex flex-col w-full rounded-md group hover:bg-gray-100 dark:hover:bg-gray-800">
<div class="text-gray-500 group-hover:text-primary rounded-md p-3 text-center">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-mobile inline-block w-16 h-16 stroke-current stroke-3/2 mx-auto" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none" />
<rect x="7" y="4" width="10" height="16" rx="1" />
<line x1="11" y1="5" x2="13" y2="5" />
<line x1="12" y1="17" x2="12" y2="17.01" />
</svg>
</div>
<h3 class="mb-2 text-base md:text-lg group-hover:text-primary text-center">
手机APP
</h3>
<div class="prose text-center">
</div>
</div>
</div>

<div class="flex duration-300 hover:scale-105" >
<div class="flex flex-col w-full rounded-md group hover:bg-gray-100 dark:hover:bg-gray-800">
<div class="text-gray-500 group-hover:text-primary rounded-md p-3 text-center">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-terminal inline-block w-16 h-16 stroke-current stroke-3/2 mx-auto" width="24" height="24" viewBox="0 0 1024 1024" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path
        d="M523.4 936h-22.8l-4.5-1.8c-3.4-1.4-84.8-34.7-167.4-88.4C212.8 770.5 154 694.5 154 620V197.5c0-20.3 16.4-37 37.2-38.7L495.3 88h20.5l316.8 70.8c20.9 1.6 37.3 18.3 37.3 38.7V620c0 74.6-58.8 150.6-174.6 225.8-82.7 53.7-164 87-167.4 88.4l-4.5 1.8z m-12.9-50h3c17.8-7.6 87.1-38.2 155.2-82.6C737.7 758.5 820 690.3 820 620V207.2L510.3 138H501l-297 69.2V620c0 70.3 82.3 138.6 151.3 183.5 68.1 44.3 137.4 74.9 155.2 82.5z"
        fill="currentColor" p-id="4788"></path>
    <path
        d="M693.5 394.1c-14.4-16.6-39.8-18.5-56.4-4.1L455.5 547 392 467.6c-13.7-17.2-39-20-56.2-6.3-17.2 13.7-20 39-6.3 56.2l88.9 111.2c12.7 15.9 35.2 19.5 52.1 9.1 3-1.5 5.9-3.3 8.5-5.6l210.3-181.8c16.7-14.3 18.6-39.7 4.2-56.3z"
        fill="currentColor" p-id="4789"></path>
</svg>

</div>
<h3 class="mb-2 text-base md:text-lg group-hover:text-primary text-center">
安全
</h3>
<div class="prose text-center">
</div>
</div>
</div>

<div class="flex duration-300 hover:scale-105" style="display:none;">
<div class="flex flex-col w-full rounded-md group hover:bg-gray-100 dark:hover:bg-gray-800">
<div class="text-gray-500 group-hover:text-primary rounded-md p-3 text-center">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-gift inline-block w-16 h-16 stroke-current stroke-3/2 mx-auto" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none" />
<rect x="3" y="8" width="18" height="4" rx="1" />
<line x1="12" y1="8" x2="12" y2="21" />
<path d="M19 12v7a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-7" />
<path d="M7.5 8a2.5 2.5 0 0 1 0 -5a4.8 8 0 0 1 4.5 5a4.8 8 0 0 1 4.5 -5a2.5 2.5 0 0 1 0 5" />
</svg>
</div>
<h3 class="mb-2 text-base md:text-lg group-hover:text-primary text-center">
Awesomazing
</h3>
<div class="prose text-center">
</div>
</div>
</div>
</div>
</div>
</div>
<div id="contact"></div>
<div class="bg-primary-darker text-primary-lighter py-8 md:py-24">
<div class="xl:container xl:mx-auto md:px-6 px-4">
<div class="w-5/6 md:w-3/4 lg:w-2/3 xl:w-1/2 text-center mx-auto mb-16">
<div class="text-xs md:text-sm opacity-75 font-semibold uppercase tracking-wide text-gray-300">
更多关于我们的信息
</div>
<h2 class="mt-1 tracking-tight leading-tighter text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-gray-100">
联络我们
</h2>
<div class="mt-3 text-base md:text-lg lg:text-xl prose">
<p>wikipali@126.com</p>
<p><span>请点击<a href="https://wj.qq.com/s2/13364158/ba70/" target="_blank" rel="noreferrer">腾讯问卷</a>填写您的反馈</span></p>
</div>
</div>
<div class="max-w-xl mx-auto text-gray-700" style="display:none;">
<form name="contact" action="#contact-us" method="POST" id="contact" class=" ">
<div class="form-field  ">
<div class="form-data" data-grav-field="text" data-grav-disabled data-grav-default="null">
<div class="form-input-wrapper ">
<input name="data[name]" value type="text" class="form-input " placeholder="Your&#x20;full&#x20;name" autocomplete="on" required="required" />
</div>
</div>
</div>
<div class="form-field  ">
<div class="form-data" data-grav-field="email" data-grav-disabled data-grav-default="null">
<div class="form-input-wrapper ">
<input name="data[email]" value type="email" class="form-input " placeholder="Your&#x20;email&#x20;address" required="required" />
</div>
</div>
</div>
<div class="form-field  ">
<div class="form-data" data-grav-field="text" data-grav-disabled data-grav-default="null">
<div class="form-input-wrapper ">
<input name="data[phone]" value type="text" class="form-input " placeholder="Your&#x20;phone&#x20;number&#x20;&#x28;optional&#x29;" />
</div>
</div>
</div>
<div class="form-field  ">
<div class="form-data" data-grav-field="textarea" data-grav-disabled data-grav-default="null">
<div class="form-textarea-wrapper  ">
<textarea name="data[message]" class="form-textarea  " placeholder="Your message" required="required" rows="4"></textarea>
</div>
</div>
</div>
<input type="hidden" name="__form-name__" value="contact" />
<input type="hidden" name="__unique_form_id__" value="ix2wof4ukdnschp3i1bc" />
<input type="hidden" name="form-nonce" value="a4618725adb330b36829801d1b7a11d2" />
<div class="text-center">
<button type="submit" class="form-button no-default-style text-white bg-gray-700 hover:bg-primary">Submit Form</button>
</div>
</form>
</div>
</div>
</div>
</div>
</section>
<footer class="bg-gray-100 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 text-sm">
<div class="xl:container xl:mx-auto md:px-6 px-4 py-8">
<div class="relative flex flex-col md:flex-row justify-between min-h-16 text-gray-600 dark:text-gray-500">
    <div style="display:flex;">
    @if(!empty(config('mint.app.icp_code')))
    <span>ICP:<a href="https://beian.miit.gov.cn/" target="_blank" rel="noreferrer">{{ config('mint.app.icp_code') }}</a></span>
    <span style="display:flex;">
        <img alt="code" src="{{ URL::asset('assets/images/logo_mps.png') }}" style="width: 20px; height: 20px;margin:0 12px;"/>
        @if(empty(config('mint.app.mps_code')))
        <span>滇公网安备[审批中]号</span>
        @else
        <span>{{ config('mint.app.mps_code') }}</span>
        @endif
    </span>
    @endif
    </div>
</div>
<div style="display:none;" class="relative flex flex-col md:flex-row justify-between min-h-16 text-gray-600 dark:text-gray-500">
    <div class="flex font-medium space-x-6 md:space-x-8 items-center justify-center mb-6 md:mb-0 md:justify-start ">
    <a href="/typhoon/onepage/#" class="hover:text-primary transition duration-300">Terms &amp; Conditions</a>
    <a href="/typhoon/onepage/#" class="hover:text-primary transition duration-300">Privacy Policy</a>
    </div>
    <div class="flex social mb-2 space-x-2 items-center justify-center mb-6 md:mb-0 md:justify-start">
    <a href="https://twitter.com/getgrav" aria-label="twitter" class="text-twitter  hover:filter hover:brightness-110 transition duration-300">
    <svg class="inline-block w-8 h-8 fill-current stroke-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 960 960"><path d="M480 0c133.333 0 246.667 46.667 340 140s140 206.667 140 340c0 132-46.667 245-140 339S613.333 960 480 960c-132 0-245-47-339-141S0 612 0 480c0-133.333 47-246.667 141-340S348 0 480 0m196 392c20-14.667 35.333-30.667 46-48-21.333 8-39.333 12.667-54 14 20-12 34-29.333 42-52-20 10.667-40 18-60 22-18.667-18.667-42-28-70-28-26.667 0-49 9.333-67 28s-27 40.667-27 66c0 1.333.333 4.667 1 10s1 9.333 1 12c-80-4-144.667-37.333-194-100-9.333 16-14 32-14 48 0 33.333 14.667 59.333 44 78-17.333 0-32-4-44-12v2c0 22.667 7 42.667 21 60s32.333 28 55 32c-10.667 2.667-18.667 4-24 4-8 0-14-.667-18-2 13.333 44 42.667 66 88 66-33.333 26.667-72.667 40-118 40h-22c45.333 28 93.333 42 144 42 81.333 0 146.667-27.667 196-83s74-117.667 74-187v-12" /></svg>
    </a>
    <a href="https://github.com/getgrav" aria-label="github" class="text-github dark:text-gray-500 hover:filter hover:brightness-110 transition duration-300">
    <svg class="inline-block w-8 h-8 fill-current stroke-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 960 960"><path d="M480 476c10.667 0 24.667-.667 42-2s31-2 41-2 20.667 1.333 32 4c11.333 2.667 21 7.333 29 14 17.333 17.333 26 37.333 26 60 0 42.667-14.333 70.667-43 84-28.667 13.333-71 20-127 20s-98.333-6.667-127-20-43-41.333-43-84c0-22.667 8.667-42.667 26-60 8-6.667 17.667-11.333 29-14 11.333-2.667 22-4 32-4s23.667.667 41 2 31.333 2 42 2m-80 128c9.333 0 17-4.667 23-14s9-20 9-32c0-30.667-10.667-46-32-46s-32 15.333-32 46c0 12 3 22.667 9 32 6 9.333 13.667 14 23 14m160 0c9.333 0 17.333-4.667 24-14 6.667-9.333 10-20 10-32 0-13.333-3.333-24.333-10-33-6.667-8.667-14.667-13-24-13-21.333 0-32 15.333-32 46 0 12 3 22.667 9 32 6 9.333 13.667 14 23 14M480 0c133.333 0 246.667 46.667 340 140s140 206.667 140 340c0 132-46.667 245-140 339S613.333 960 480 960c-132 0-245-47-339-141S0 612 0 480c0-133.333 47-246.667 141-340S348 0 480 0m44 676c125.333 0 188-61.333 188-184 0-37.333-12.667-70-38-98 2.667-2.667 3-16.333 1-41s-7.667-48.333-17-71c-29.333 4-67.333 21.333-114 52-13.333-4-34.667-6-64-6-26.667 0-48 2-64 6-20-13.333-39.667-24.333-59-33-19.333-8.667-33-14.333-41-17l-14-2c-9.333 22.667-15 46.333-17 71s-1.667 38.333 1 41c-25.333 28-38 60.667-38 98 0 122.667 62.667 184 188 184h88" /></svg>
    </a>
    </div>
</div>
<div class="relative flex flex-col md:flex-row justify-between mt-0 md:mt-6 text-gray-600 dark:text-gray-500 md:items-center">
<div class="text-center md:text-left mb-6 md:mb-0">
<p>本页面模版基于<a href="https://getgrav.org/premium/typhoon/docs">Grav Typhoon</a>制作</p>
</div>
<div class="max-w-64 mx-auto md:mx-0">
<div class="theme-chooser flex items-center rounded-md border border-gray-300 dark:border-gray-600 pl-2 pr-0">
<span>
<span x-cloak :class="{'inline-block' : appearance === 'system', 'hidden': appearance !== 'system'}">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-desktop inline-block w-6 h-6 stroke-current" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none" />
<rect x="3" y="4" width="18" height="12" rx="1" />
<line x1="7" y1="20" x2="17" y2="20" />
<line x1="9" y1="16" x2="9" y2="20" />
<line x1="15" y1="16" x2="15" y2="20" />
</svg>
</span>
<span x-cloak :class="{'inline-block' : appearance === 'light', 'hidden': appearance !== 'light'}">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-sun inline-block w-6 h-6 stroke-current" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none" />
<circle cx="12" cy="12" r="4" />
<path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7" />
</svg>
</span>
<span x-cloak :class="{'inline-block' : appearance === 'dark', 'hidden': appearance !== 'dark'}">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-moon inline-block w-6 h-6 stroke-current" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none" />
<path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z" />
</svg>
</span>
</span>
<select x-model="appearance" @change="typhoonStore({ appearance: $event.target.value });" class="form-select focus:ring-transparent focus:outline-none focus:shadow-none" aria-label="Change color theme">
<option value="system" :selected="appearance === 'system'">System</option>
<option value="light" :selected="appearance === 'light'">Light</option>
<option value="dark" :selected="appearance === 'dark'">Dark</option>
</select>
</div>
</div>
</div>
</div>
</footer>
<div :class="{ 'invisible': !show_mobile_nav, 'opacity-100': show_mobile_nav }" class="mobile-nav invisible z-20 overflow-hidden transition duration-500 h-screen w-screen absolute top-0 flex flex-col items-center justify-around absolute opacity-0 bg-gray-800 transition duration-500">
<div class="overflow-y-auto w-full py-12 pl-2 pr-12 sm:p-12">
<ul class="flex flex-col text-gray-300 text-left w-full">
@foreach ($nav as $item)
<li x-data="{ selected: true }" class="text-lg pl-2 border-t border-gray-700 py-2 active ">
<div class="flex w-full h-full">
<a class="w-full transition duration-300 hover:text-primary" href="{{ $item['link'] }}">{{ $item['title'] }}</a>
</div> </li>
@endforeach
</ul>
</div>
<div class="absolute top-2 right-2">
<button @click="show_mobile_nav = false" aria-label="Close button" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-primary focus:outline-none focus:text-gray-800 transition duration-150 ease-in-out">
<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-x inline-block h-8 w-8 text-gray-300 hover:text-white" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
<path stroke="none" d="M0 0h24v24H0z" fill="none" />
<line x1="18" y1="6" x2="6" y2="18" />
<line x1="6" y1="6" x2="18" y2="18" />
</svg>
</button>
</div>
</div>
</div>
<script>
window.GravForm = window.GravForm || {};
    window.GravForm.config = {
        current_url: '/typhoon/onepage',
        current_params: [],
        param_sep: ':',
        base_url_relative: '/typhoon/onepage',
        form_nonce: 'a4618725adb330b36829801d1b7a11d2',
        session_timeout: 1800
    };
    window.GravForm.translations = Object.assign({}, window.GravForm.translations || {}, { PLUGIN_FORM: {} });
</script>
<script src="/assets/typhoon/js/glightbox.min.js"></script>
<script src="/assets/typhoon/js/appearance.js"></script>
<script>
const lightbox = GLightbox({"selector":"[rel=\"lightbox\"], .glightbox","width":"90vw","height":"auto"});
</script>

<script>
    let api = "{{ $api }}/auth/current";
    const key = "token";
    let token = sessionStorage.getItem(key);
    if(token){
        console.log('api',api);
        const response = fetch(
            api,
            {
                credentials: "include",
                headers: {
                    Authorization: 'Bearer ' + token,
                    "Content-Type": "application/json; charset=utf-8",
                    },
                mode: "cors",
                method: 'GET',
            }
        ).then((response) => response.json())
        .then((json) => {
            if(json.ok){
                document.getElementById('nickname').innerHTML = 'Hi! ' + json.data.nickName;
            }else{
                document.getElementById("sign_in").style.display = "block";
            }
            console.log('user',json);
        });
    }else{
        console.error('no token');
        document.getElementById("sign_in").style.display = "block";

    }
</script>

</body>
</html>
