<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApiLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        if (defined('LARAVEL_START'))
        {
            $api = [];
            $api[] = date("h:i:sa",LARAVEL_START);
            $api[] = round((microtime(true) - LARAVEL_START)*1000,2);
            $api[] = $request->method();
            $api[] = $request->path();
            Storage::disk('local')->append("logs/api/".date("Y-m-d").".log",\implode(',',$api) );
        }
        return $response;
    }
}
