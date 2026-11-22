{{-- resources/views/library/tipitaka/download.blade.php
     下载页（TipitakaDownloadController）：一部作品（$mode = work），或整个 channel 打包（$mode = channel，只有「全部」一种范围）。
     四种格式各一张卡片。已就绪的直接下载；其余点击后调 POST /api/v3/tipitaka-exports 发起，轮询状态，
     就绪后自动下载（同时给一个手动下载的链接兜底）。
     变量：$mode、$channelInfo、$request（POST 的 channel / work）、$formats[{key,name,description,state}]、$backUrl；
           work：$book（bookCard() + pali_title / updated_at）；
           channel：$groups[分类路径 => [{title,progress,url}]]
--}}
@extends('layouts.library')

@section('title', ($mode === 'work' ? $book['title'] : $channelInfo['name']) . ' · ' . __('library.download_title') . ' · ' . __('library.site_name'))

@push('styles')
@vite('resources/css/modules/tipitaka.css')
@endpush

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('library.home') }}">{{ __('library.home') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('library.tipitaka.index') }}">{{ __('library.tipitaka') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('library.tipitaka.channels.index') }}">{{ __('library.channels') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('library.tipitaka.channels.show', ['channel' => $channelInfo['uid']]) }}">{{ $channelInfo['name'] }}</a></li>
@if($mode === 'work')
<li class="breadcrumb-item"><a href="{{ $backUrl }}">{{ $book['title'] }}</a></li>
@endif
<li class="breadcrumb-item active">{{ __('library.download_title') }}</li>
@endsection

@section('content')
<div class="page-body">
    <div class="container-xl tipitaka-download">

        <div class="wiki-card tipitaka-download__head">
            @if($mode === 'work')
            <x-ui.book-cover
                :image="$book['cover']"
                :gradient="$book['cover_gradient']"
                :title="$book['pali_title']"
                :subtitle="$book['subTitle'] ?? ''"
                size="md"
                :style3d="false"
                class="tipitaka-download__cover" />
            <div class="tipitaka-download__info">
                <h1 class="tipitaka-download__title">{{ $book['title'] }}</h1>
                @if($book['pali_title'] !== $book['title'])
                <div class="tipitaka-work-pali">{{ $book['pali_title'] }}</div>
                @endif
                <div class="tipitaka-download__meta">
                    <span>{{ __('library.channel') }} <a href="{{ route('library.tipitaka.channels.show', ['channel' => $channelInfo['uid']]) }}">{{ $channelInfo['name'] }}</a></span>
                    <span>{{ $book['language'] }}</span>
                    @if($book['progress'] !== null)
                    <span>{{ __('library.progress') }} {{ $book['progress'] }}%</span>
                    @endif
                    @if(!empty($book['updated_at']))
                    <span>{{ __('library.updated') }} {{ $book['updated_at'] }}</span>
                    @endif
                </div>
            </div>
            @else
            <div class="tipitaka-download__info">
                <h1 class="tipitaka-download__title">{{ __('library.download_all_title', ['name' => $channelInfo['name']]) }}</h1>
                <div class="tipitaka-download__meta"><span>{{ __('library.download_all_hint') }}</span></div>
            </div>
            @endif
            <a href="{{ $backUrl }}" class="btn btn-sm tipitaka-download__back">‹ {{ __('library.download_back') }}</a>
        </div>

        <h2 class="tipitaka-download__section">
            {{ __('library.download_choose_format') }}
            <small>{{ __('library.download_format_note') }}</small>
        </h2>

        @php
        $i18n = [
            'ready' => __('library.download_state_ready'),
            'queued' => __('library.download_state_queued'),
            'running' => __('library.download_state_running'),
            'failed' => __('library.download_state_failed'),
            'download' => __('library.download_button'),
            'retry' => __('library.download_retry'),
        ];
        @endphp
        <div class="tipitaka-download__formats" data-download data-request='@json($request)' data-i18n='@json($i18n)'>
            @foreach($formats as $format)
            @php
            $state = $format['state'];
            $ready = $state['status'] === 'ready';
            @endphp
            <div class="wiki-card tipitaka-download__format" data-format="{{ $format['key'] }}"
                data-state="{{ $ready ? 'ready' : ($state['too_large'] ?? false ? 'too_large' : 'absent') }}"
                @if($ready) data-url="{{ $state['download_url'] }}" @endif>
                <div class="tipitaka-download__format-top">
                    <span class="tipitaka-download__icon tipitaka-download__icon--{{ $format['key'] }}">{{ $format['key'] === 'markdown' ? 'MD' : strtoupper($format['key']) }}</span>
                    <div>
                        <h3>{{ $format['name'] }}</h3>
                        <p>{{ $format['description'] }}</p>
                    </div>
                </div>
                <div class="tipitaka-download__file"><i class="ti ti-file-zip"></i> {{ $state['filename'] }}</div>
                <div class="tipitaka-download__foot">
                    <span class="tipitaka-download__state" data-state-text>
                        <span class="tipitaka-download__dot"></span>
                        @if($ready)
                        {{ __('library.download_state_ready', ['size' => \Illuminate\Support\Number::fileSize($state['size'], 1), 'date' => substr((string) $state['expires_at'], 0, 10)]) }}
                        @elseif($state['too_large'] ?? false)
                        {{ __('library.download_state_too_large') }}
                        @else
                        {{ __('library.download_state_absent') }}
                        @endif
                    </span>
                    <button type="button" class="btn btn-sm {{ $ready ? 'btn-primary' : '' }}" data-start @disabled($state['too_large'] ?? false)>
                        <i class="ti ti-download me-1"></i>{{ $ready ? __('library.download_button') : __('library.download_generate_button') }}
                    </button>
                </div>
                <div class="tipitaka-download__progress" data-progress hidden>
                    <div class="tipitaka-download__progress-text"><span data-progress-message></span><span data-progress-percent></span></div>
                    <div class="progress progress-sm"><div class="progress-bar" data-progress-bar style="width: 0%"></div></div>
                </div>
                <a class="tipitaka-download__manual" data-manual hidden>{{ __('library.download_manual') }}</a>
            </div>
            @endforeach
        </div>

        <div class="tipitaka-download__hint"><i class="ti ti-info-circle"></i><span>{{ __('library.download_hint') }}</span></div>

        @if($mode === 'channel')
        <h2 class="tipitaka-download__section">
            {{ __('library.download_included_works') }}
            <small>{{ __('library.download_works_count', ['count' => array_sum(array_map('count', $groups))]) }}</small>
        </h2>
        <div class="wiki-card tipitaka-download__works">
            @foreach($groups as $label => $works)
            <details @if($loop->first) open @endif>
                <summary><b>{{ $label }}</b><span>{{ __('library.download_works_count', ['count' => count($works)]) }}</span></summary>
                <ul>
                    @foreach($works as $work)
                    <li><a href="{{ $work['url'] }}">{{ $work['title'] }}</a><span>{{ $work['progress'] }}%</span></li>
                    @endforeach
                </ul>
            </details>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function() {
        const root = document.querySelector('[data-download]');
        if (!root) return;
        const request = JSON.parse(root.dataset.request);
        const t = JSON.parse(root.dataset.i18n);
        const fill = (text, values) => text.replace(/:(\w+)/g, (m, k) => values[k] ?? m);
        const fileSize = (bytes) => bytes >= 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB';
        const json = async (response) => {
            const body = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(body.detail || response.statusText);
            return body.data;
        };

        const setState = (card, cls, text) => {
            const el = card.querySelector('[data-state-text]');
            el.className = 'tipitaka-download__state tipitaka-download__state--' + cls;
            el.innerHTML = '<span class="tipitaka-download__dot"></span>';
            el.append(text);
        };
        const showProgress = (card, data) => {
            card.querySelector('[data-progress]').hidden = false;
            const percent = Math.round((data.progress || 0) * 100);
            card.querySelector('[data-progress-bar]').style.width = percent + '%';
            card.querySelector('[data-progress-percent]').textContent = data.status === 'running' ? percent + '%' : '';
            card.querySelector('[data-progress-message]').textContent = data.status === 'running' ? (data.message || t.running) : t.queued;
        };
        // 浏览器下载附件不会离开本页；被拦下时还有手动链接
        const deliver = (card, data, auto) => {
            card.dataset.state = 'ready';
            card.dataset.url = data.download_url;
            card.querySelector('[data-progress]').hidden = true;
            setState(card, 'ready', fill(t.ready, { size: fileSize(data.size), date: (data.expires_at || '').slice(0, 10) }));
            const button = card.querySelector('[data-start]');
            button.disabled = false;
            button.classList.add('btn-primary');
            button.lastChild.textContent = t.download;
            if (auto) {
                const manual = card.querySelector('[data-manual]');
                manual.href = data.download_url;
                manual.hidden = false;
                window.location.href = data.download_url;
            }
        };
        const fail = (card, message) => {
            card.querySelector('[data-progress]').hidden = true;
            setState(card, 'failed', message || t.failed);
            const button = card.querySelector('[data-start]');
            button.disabled = false;
            button.lastChild.textContent = t.retry;
        };

        const poll = async (card, id, attempt) => {
            try {
                const data = await json(await fetch('/api/v3/tipitaka-exports/' + id, { headers: { Accept: 'application/json' } }));
                if (data.status === 'ready') return deliver(card, data, true);
                if (data.status === 'failed') return fail(card, data.message);
                showProgress(card, data);
            } catch (e) {
                return fail(card, e.message);
            }
            // 1s, 2s, 3s，之后每 5 秒
            setTimeout(() => poll(card, id, attempt + 1), [1000, 2000, 3000][attempt] ?? 5000);
        };

        root.addEventListener('click', async (event) => {
            const button = event.target.closest('[data-start]');
            if (!button) return;
            const card = button.closest('[data-format]');
            if (card.dataset.state === 'ready' && card.dataset.url) {
                window.location.href = card.dataset.url;
                return;
            }
            button.disabled = true;
            card.querySelector('[data-manual]').hidden = true;
            try {
                const data = await json(await fetch('/api/v3/tipitaka-exports', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                    body: JSON.stringify({ ...request, format: card.dataset.format }),
                }));
                if (data.status === 'ready') return deliver(card, data, true);
                card.dataset.state = 'busy';
                showProgress(card, data);
                setTimeout(() => poll(card, data.id, 0), 1000);
            } catch (e) {
                fail(card, e.message);
            }
        });
    })();
</script>
@endpush
