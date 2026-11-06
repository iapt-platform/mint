{{-- 按 type 分组的 channel 列表 + 左栏（类型锚点、语言过滤）。作品版本页用。
     变量：$groups（TipitakaCatalogService::groupByType()，每行要有 url）、$languages、$selectedLang --}}
        <aside class="wiki-sidebar-left">
            <div class="wiki-sidebar-section">
                <div class="wiki-sidebar-title">{{ __('library.type') }}</div>
                <ul class="wiki-cat-list">
                    @foreach($groups as $group)
                    <li>
                        <a href="#type-{{ $group['type'] }}">
                            {{ $group['label'] }}
                            <span class="wiki-cat-count">{{ count($group['channels']) }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            <div class="wiki-sidebar-section">
                <div class="wiki-sidebar-title">{{ __('library.language') }}</div>
                <div class="tipitaka-filter-pills">
                    @foreach($languages as $opt)
                    <a href="{{ request()->fullUrlWithQuery(['channel_lang' => $opt['value'] === 'all' ? null : $opt['value']]) }}"
                        class="tipitaka-pill {{ $selectedLang === $opt['value'] ? 'tipitaka-pill--active' : '' }}">
                        {{ $opt['label'] }}
                        <span class="tipitaka-pill-count">{{ $opt['count'] }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
        </aside>

        <main class="wiki-main">
            @forelse($groups as $group)
            <div class="wiki-card" id="type-{{ $group['type'] }}">
                <div class="wiki-sidebar-title">
                    {{ $group['label'] }} <span class="wiki-cat-count">{{ count($group['channels']) }}</span>
                </div>
                @foreach($group['channels'] as $channel)
                @include('library.tipitaka.channels._row', ['channel' => $channel])
                @endforeach
            </div>
            @empty
            <div class="wiki-card"><x-ui.empty-state :title="__('library.no_channels')" /></div>
            @endforelse
        </main>
