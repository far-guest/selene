<?php

namespace Selene\Middleware;


use Closure;

class AccessSelene
{
    public function handle($request, Closure $next, ...$guards)
    {
        if (\Gate::has('AccessSelene') && !\Gate::check('AccessSelene')) {
            abort(403);
        }

        return $next($request);
    }
}