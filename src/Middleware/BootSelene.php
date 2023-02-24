<?php

namespace Selene\Middleware;


use Closure;

class BootSelene
{
    public function handle($request, Closure $next, ...$guards)
    {
        app('selene')->boot();
        dd(app());
        return $next($request);
    }
}