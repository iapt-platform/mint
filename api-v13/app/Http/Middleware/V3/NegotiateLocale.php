<?php

namespace App\Http\Middleware\V3;

use App\Http\Middleware\SetLocale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * v3 的语言协商：按 `Accept-Language` 决定 `__()` 用哪种语言。
 *
 * 只挂在 v3 路由组上（routes/api.php）。之前 api 组没有任何语言协商，`__()` 永远取
 * en，中文界面里显示英文错误（dashboard-test BUG-5）。
 *
 * 继承 web 组的 {@see SetLocale}，复用它的标签解析（q 值排序、大小写不敏感、
 * zh-CN/zh-TW 归简繁、th-TH → th），但**只读请求头**：
 *
 * - 不读 `?lang=` / cookie：API 的调用方（v6、mobile）明确知道界面语言，直接放请求头里；
 *   `?lang=` 在不少 v3 端点上已有业务含义（如译文语言），不能拿来当界面语言。
 * - 不读写 cookie / session：API 无状态，api 组也没有 session。尤其不能读 web 组写的
 *   `language` cookie——Laravel 页面与 dashboard-v6 同域，这个 cookie 会跟着 v6 的请求
 *   一起发来，若它优先，用户在 Laravel 页面选过的语言就会盖过 v6 明确声明的界面语言。
 *   Laravel 页面将来要调 v3 并显示错误文案，也应像 v6 一样自己带 Accept-Language
 *   （取 `<html lang>`，即 `app()->getLocale()`）。
 *
 * 匹配不上就用 `config('mint.default_language')`。Problem Details 的 `title` 按 RFC 9457
 * 保持稳定英文，不受这里影响；本地化的是 `detail` 与 `errors`。
 */
class NegotiateLocale extends SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('mint.languages'));

        // 兜底用 mint.default_language 而不是 app.locale：App::setLocale() 会顺带改写
        // config('app.locale')，常驻进程（Octane、队列）里上一个请求的语言会串到下一个
        App::setLocale($this->getBrowserLocale($request, $supported) ?? config('mint.default_language', 'en'));

        $response = $next($request);
        // 响应随 Accept-Language 而变，告诉缓存别混用
        $response->headers->set('Vary', trim($response->headers->get('Vary').', Accept-Language', ', '));
        $response->headers->set('Content-Language', App::getLocale());

        return $response;
    }
}
