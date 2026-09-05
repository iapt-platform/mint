<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 支持的语言（config 里是 en / zh-Hans 这类带大小写的写法）
        $supportedLocales = array_keys(config('mint.languages'));
        $defaultLocale = config('app.locale', 'en');

        // 1️⃣ URL 参数 ?lang=
        $locale = $this->matchLocale($request->query('lang'), $supportedLocales);

        // 2️⃣ Cookie
        if (! $locale) {
            $locale = $this->matchLocale(Cookie::get('language'), $supportedLocales);
        }

        // 3️⃣ 浏览器 / 操作系统语言（Accept-Language 由浏览器按系统界面语言生成）
        if (! $locale) {
            $locale = $this->getBrowserLocale($request, $supportedLocales);
        }

        // 4️⃣ 兜底
        $locale = $locale ?? $defaultLocale;

        // 5️⃣ 应用语言
        App::setLocale($locale);

        // 6️⃣ 持久化（可选）
        session()->put('locale', $locale);
        Cookie::queue('language', $locale, 60 * 24 * 365); // 1 年

        return $next($request);
    }

    /**
     * 从 Accept-Language 头中解析浏览器语言
     *
     * @param  string[]  $supportedLocales
     */
    protected function getBrowserLocale(Request $request, array $supportedLocales): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');

        if (! $acceptLanguage) {
            return null;
        }

        // zh-CN,zh;q=0.9,en;q=0.8 → 按 q 值从高到低排序
        $languages = [];

        foreach (explode(',', $acceptLanguage) as $part) {
            $pieces = explode(';', trim($part));
            $tag = strtolower(trim($pieces[0]));

            if ($tag === '' || $tag === '*') {
                continue;
            }

            $quality = 1.0;

            if (isset($pieces[1]) && preg_match('/q=([0-9.]+)/', $pieces[1], $matches)) {
                $quality = (float) $matches[1];
            }

            $languages[] = ['tag' => $tag, 'quality' => $quality];
        }

        usort($languages, fn ($a, $b) => $b['quality'] <=> $a['quality']);

        foreach ($languages as $language) {
            if ($locale = $this->matchLocale($language['tag'], $supportedLocales)) {
                return $locale;
            }
        }

        return null;
    }

    /**
     * 把一个语言标签匹配到受支持的语言，匹配不上返回 null。
     * 大小写不敏感；zh-CN / zh-SG 归为简体，zh-TW / zh-HK / zh-MO 归为繁体；
     * 其余按主语言回退，如 th-TH → th。
     *
     * @param  string[]  $supportedLocales
     */
    protected function matchLocale(?string $tag, array $supportedLocales): ?string
    {
        if (! $tag) {
            return null;
        }

        $tag = strtolower(str_replace('_', '-', trim($tag)));

        // lowercase => 原始写法，便于大小写不敏感匹配
        $lookup = array_combine(array_map('strtolower', $supportedLocales), $supportedLocales);

        // 精确匹配：en、zh-hans……
        if (isset($lookup[$tag])) {
            return $lookup[$tag];
        }

        // 中文按地区码判断简繁
        if (str_starts_with($tag, 'zh')) {
            $traditional = str_contains($tag, 'hant')
                || (bool) preg_match('/\-(tw|hk|mo)\b/', $tag);

            $chinese = $traditional ? 'zh-hant' : 'zh-hans';

            return $lookup[$chinese] ?? null;
        }

        // 主语言回退：th-th → th
        $primary = explode('-', $tag)[0];

        return $lookup[$primary] ?? null;
    }
}
