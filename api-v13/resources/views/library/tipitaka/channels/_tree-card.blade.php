{{-- 目录树里的一张作品卡片。变量：$title（可为 null）、$works（list<{sn, title, progress|null, url}>） --}}
@if(!empty($works))
<div class="tipitaka-tree__card">
    @if($title)
    <div class="tipitaka-tree__card-title">{{ $title }}</div>
    @endif
    <ol>
        @foreach($works as $work)
        <li data-work="{{ $work['title'] }}" class="{{ $work['progress'] === null ? 'is-missing' : '' }}">
            @if($work['progress'] === null)
            <span class="tipitaka-tree__work">
                <span class="tipitaka-tree__work-title">{{ $work['title'] }}</span>
                <span class="tipitaka-tree__work-missing">{{ __('library.not_included') }}</span>
            </span>
            @else
            <a href="{{ $work['url'] }}" class="tipitaka-tree__work">
                <span class="tipitaka-tree__work-title">{{ $work['title'] }}</span>
                <x-ui.progress-bar :value="$work['progress']" class="tipitaka-tree__work-progress" />
            </a>
            @endif
        </li>
        @endforeach
    </ol>
</div>
@endif
