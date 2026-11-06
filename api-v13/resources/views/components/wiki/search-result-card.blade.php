{{-- resources/views/components/wiki/search-result-card.blade.php --}}
@props(['result', 'lang'])

<div class="wiki-search-card">

    <div class="wiki-search-card-header">
        <a class="wiki-search-card-title"
            href="
            @if($result['type']==='term')
            {{ route('library.wiki.show', [$result['lang'], $result['id']]) }}
            @endif
            @if($result['type']==='tipitaka')
            {{-- channel 是从 OpenSearch 文档 id 里拆出来的，不是 uuid 时生成新路由会抛异常，退回旧地址 --}}
            {{ \Illuminate\Support\Str::isUuid($result['chapter']['channel'] ?? null)
                ? route('library.tipitaka.channels.chapter', ['channel' => $result['chapter']['channel'], 'chapter' => $result['chapter']['id']])
                : url('/library/tipitaka/'.$result['chapter']['id'].'/read') }}
            @endif
            ">
            {{ $result['title'] }}
            <span class="wiki-search-card-word">{{ $result['type'] }}</span>
        </a>
        <x-wiki.quality-badge :quality="$result['quality']" />
    </div>

    <p class="wiki-search-card-snippet">{!! $result['snippet'] !!}</p>

    <div class="wiki-search-card-meta">
        <span class="wiki-search-card-category">{{ $result['category'] }}</span>
        <span class="wiki-search-card-sep">·</span>
        <span class="wiki-search-card-date">更新于 {{ $result['updated'] }}</span>
    </div>

</div>
