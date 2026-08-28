{{-- resources/views/components/home/card.blade.php
     WikiPāli 首页通栏卡片。底图满铺，文字压在图上。
     available && href 非空 → 渲染链接按钮；否则渲染不可点击的状态角标。

     底图按「存在的文件」动态输出：优先 AVIF → WebP → JPEG 兜底。
     有底图时直接显示底图（不加颜色混合蒙版）；素材未就位时才用蒙版纯色块占位，不引用任何不存在的文件，
     避免浏览器选中 AVIF 源却 404 导致整张图不显示。
--}}
@props([
    'slug',
    'eyebrow',
    'title',
    'lead',
    'image',
    'tint',
    'align',
    'href' => null,
    'cta',
    'available' => false,
    'eager' => false,
])

@php
    $sizes = [640, 1280, 2560];
    $formats = ['avif' => 'image/avif', 'webp' => 'image/webp', 'jpg' => 'image/jpeg'];

    // 收集磁盘上实际存在的底图文件
    $byFormat = []; // ext => [ ['url'=>..., 'width'=>...], ... ]
    foreach ($formats as $ext => $mime) {
        $found = [];
        foreach ($sizes as $width) {
            $url = "/img/home/{$image}-{$width}.{$ext}";
            if (is_file(public_path("img/home/{$image}-{$width}.{$ext}"))) {
                $found[] = ['url' => $url, 'width' => $width];
            }
        }
        if ($found !== []) {
            $byFormat[$ext] = $found;
        }
    }

    // 每种存在格式的 srcset 串
    $sets = [];
    foreach ($byFormat as $ext => $entries) {
        $parts = [];
        foreach ($entries as $e) {
            $parts[] = $e['url'].' '.$e['width'].'w';
        }
        $sets[$ext] = implode(', ', $parts);
    }

    // <img> 兜底：优先 jpg，其次 webp，再次 avif；尺寸优先 1280
    $fallbackFormat = null;
    foreach (['jpg', 'webp', 'avif'] as $ext) {
        if (isset($byFormat[$ext])) {
            $fallbackFormat = $ext;
            break;
        }
    }

    $fallbackSrc = null;
    if ($fallbackFormat !== null) {
        foreach ($byFormat[$fallbackFormat] as $e) {
            if ($e['width'] === 1280) {
                $fallbackSrc = $e['url'];
                break;
            }
        }
        $fallbackSrc ??= $byFormat[$fallbackFormat][0]['url'];
    }

    $titleId = "{$slug}-title";
@endphp

<section id="card-{{ $slug }}"
    class="home-card home-card--{{ $align }}"
    style="--card-tint: {{ $tint }}"
    aria-labelledby="{{ $titleId }}">

    @if ($fallbackSrc !== null)
    <picture class="home-card__media-wrap">
        @if (isset($sets['avif']))
        <source type="image/avif" srcset="{{ $sets['avif'] }}" sizes="100vw">
        @endif
        @if (isset($sets['webp']))
        <source type="image/webp" srcset="{{ $sets['webp'] }}" sizes="100vw">
        @endif
        <img class="home-card__media"
            src="{{ $fallbackSrc }}"
            @if (isset($sets[$fallbackFormat])) srcset="{{ $sets[$fallbackFormat] }}" sizes="100vw" @endif
            alt=""
            decoding="async"
            @if($eager) loading="eager" fetchpriority="high" @else loading="lazy" @endif>
    </picture>
    @else
    <div class="home-card__veil" aria-hidden="true"></div>
    @endif

    <div class="home-card__body">
        <div class="home-card__text">
            <p class="home-card__eyebrow">{{ $eyebrow }}</p>
            <h2 class="home-card__title" id="{{ $titleId }}">{{ $title }}</h2>
            <p class="home-card__lead">{{ $lead }}</p>

            @if ($available && filled($href))
                <a class="home-card__cta" href="{{ $href }}">{{ $cta }}</a>
            @else
                <span class="home-card__badge">
                    <i class="ti ti-clock" aria-hidden="true"></i>
                    {{ $cta }}
                </span>
            @endif
        </div>
    </div>
</section>
