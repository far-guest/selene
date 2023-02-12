<?php

namespace Selene\Middleware;


use Closure;

class BootSelene
{
    public function handle($request, Closure $next, ...$guards)
    {
        app('selene')->boot();

        return $next($request);
    }
}