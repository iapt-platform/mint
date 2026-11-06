{{-- 单个 channel 页的头部：头像、名字、元信息与「全部作品 / 按分类」两个标签。
     变量：$channelInfo（TipitakaChannelController::channelInfo()）、$tab（all | category）、$topCategories --}}
<div class="wiki-card tipitaka-channel-head">
    <div class="tipitaka-channel-head__main">
        <x-ui.author-avatar
            :color="\App\Services\TipitakaCatalogService::channelColor($channelInfo['uid'])"
            :initials="mb_substr($channelInfo['name'], 0, 1)"
            size="lg" />
        <div>
            <h1 class="tipitaka-channel-head__name">
                {{ $channelInfo['name'] }}
                <span class="card-book__badge">{{ __('labels.'.$channelInfo['type']) }}</span>
            </h1>
            <div class="tipitaka-channel-head__meta">
                {{ __('language.'.$channelInfo['lang']) }}
                @if($channelInfo['owner'])
                · <a href="{{ route('blog.index', ['user' => $channelInfo['owner']->username]) }}">{{ $channelInfo['owner']->nickname }}</a>
                @endif
                · {{ $channelInfo['works'] }} {{ __('library.work_unit') }}
                @if($channelInfo['updated_at'])
                · {{ __('library.updated') }} {{ $channelInfo['updated_at'] }}
                @endif
            </div>
        </div>
    </div>
    <div class="tipitaka-channel-tabs">
        <a href="{{ route('library.tipitaka.channels.show', ['channel' => $channelInfo['uid']]) }}"
            class="{{ $tab === 'all' ? 'active' : '' }}">{{ __('library.all_works') }}</a>
        <a href="{{ route('library.tipitaka.channels.category', ['channel' => $channelInfo['uid']]) }}"
            class="{{ $tab === 'category' ? 'active' : '' }}">{{ __('library.by_category') }}</a>
    </div>
</div>
