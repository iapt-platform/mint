{{-- resources/views/components/wiki/entry-header.blade.php --}}
@props(['entry'])

<div class="wiki-entry-header">

    <x-wiki.quality-badge :quality="$entry['quality']" />

    <h1 class="wiki-entry-title">{{ $entry['meaning'] }}</h1>
    <div>{{ $entry['word'] }}</div>

    {{-- 编辑者（+ 协作者，即授权代写的 AI 模型）与编辑时间 --}}
    @if(! empty($entry['editors']) || ! empty($entry['updated_at']))
    <div class="wiki-entry-editors">
        @if(! empty($entry['editors']))
        <span class="wiki-avatar-group">
            @foreach($entry['editors'] as $person)
            <span class="wiki-avatar-group__item" title="{{ $person['name'] }}">
                <x-ui.author-avatar
                    :avatar="$person['avatar']"
                    :color="$person['color']"
                    :initials="$person['initials']"
                    size="sm" />
            </span>
            @endforeach
        </span>
        <span class="wiki-entry-editors__names">{{ collect($entry['editors'])->pluck('name')->join(' & ') }}</span>
        @endif
        @if(! empty($entry['updated_at']))
        <time class="wiki-entry-editors__time"
              datetime="{{ $entry['updated_at']->toIso8601ZuluString() }}"
              title="{{ $entry['updated_at']->toDateTimeString() }}">
            {{ __('library.edited_at', ['time' => $entry['updated_at']->locale(app()->getLocale())->diffForHumans()]) }}
        </time>
        @endif
    </div>
    @endif

</div>
