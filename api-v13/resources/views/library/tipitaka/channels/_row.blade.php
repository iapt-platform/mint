{{-- channel 列表的一行。变量：$channel{uid, name, type, lang, owner?|owner_name?, updated_at?, url, works? | progress?}、$showType（可选，显示类型标签）
     owner 是 UserInfo 模型；作品版本页的行来自 TipitakaChapterChannelService，只有 owner_name（所有者可能是群组）。
     有 progress（作品版本页）时右侧显示进度条，否则显示收录作品数。 --}}
<a href="{{ $channel['url'] }}" class="tipitaka-channel-row">
    <x-ui.author-avatar
        :color="\App\Services\TipitakaCatalogService::channelColor($channel['uid'])"
        :initials="mb_substr($channel['name'], 0, 1)"
        size="sm" />
    <div class="tipitaka-channel-row__info">
        <div class="tipitaka-channel-row__name">
            {{ $channel['name'] }}
            @if($showType ?? false)<span class="card-book__badge">{{ __('labels.'.$channel['type']) }}</span>@endif
        </div>
        <div class="tipitaka-channel-row__meta">
            {{ __('language.'.$channel['lang']) }}
            @if(!empty($channel['owner_name'])) · {{ $channel['owner_name'] }} @elseif(!empty($channel['owner'])) · {{ $channel['owner']->nickname }} @endif
            @if(!empty($channel['updated_at'])) · {{ __('library.updated') }} {{ $channel['updated_at'] }} @endif
        </div>
    </div>
    @isset($channel['progress'])
    <x-ui.progress-bar :value="$channel['progress']" class="tipitaka-channel-row__progress" />
    @else
    <div class="tipitaka-channel-row__count">{{ $channel['works'] }} {{ __('library.work_unit') }}</div>
    @endisset
</a>
