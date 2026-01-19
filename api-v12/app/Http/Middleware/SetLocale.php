<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;

class SetLocale
{
    /**
     * Handle an incoming request.
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 支持的语言（Laravel 推荐小写 + 标准化）
        $supportedLocales = ['en', 'zh-hans', 'zh-hant'];
        $defaultLocale = config('app.locale', 'en');

        // 1️⃣ URL 参数 ?lang=
        $locale = $request->query('lang');

        // 2️⃣ Cookie
        if (!$locale) {
            $locale = Cookie::get('language');
        }

        // 3️⃣ 浏览器语言
        if (!$locale) {
            $locale = $this->getBrowserLocale($request, $supportedLocales);
        }

        // 4️⃣ 校验
        if (!in_array($locale, $supportedLocales, true)) {
            $locale = $defaultLocale;
        }

        // 5️⃣ 应用语言
        App::setLocale($locale);

        // 6️⃣ 持久化（可选）
        session()->put('locale', $locale);
        Cookie::queue('language', $locale, 60 * 24 * 365); // 1 年

        return $next($request);
    }

    /**
     * 从 Accept-Language 头中解析浏览器语言
     */
    protected function getBrowserLocale(Request $request, array $supportedLocales): string
    {
        $acceptLanguage = $request->header('Accept-Language');

        if (!$acceptLanguage) {
            return config('app.locale', 'en');
        }

        // zh-CN,zh;q=0.9,en;q=0.8
        $languages = array_map(
            fn($lang) => strtolower(trim(explode(';', $lang)[0])),
            explode(',', $acceptLanguage)
        );

        foreach ($languages as $lang) {
            // zh-cn → zh-hans / zh-hant
            if (str_starts_with($lang, 'zh')) {
                return str_contains($lang, 'hant') ? 'zh-hant' : 'zh-hans';
            }

            if (in_array($lang, $supportedLocales, true)) {
                return $lang;
            }
        }

        return config('app.locale', 'en');
    }
}
