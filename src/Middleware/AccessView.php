<?php

namespace Selene\Middleware;

use Selene\Exceptions\NotRegisteredException;
use Selene\Resources\Resource;
use Closure;
use Illuminate\Http\Request;
use Selene\Types\View;

class AccessView
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $param = $request->route('selene_view');

        try {
            /** @var View $view */
            $view = app('selene')->getView($param);
        } catch (NotRegisteredException $e) {
            return abort(404);
        }

        if (!$view->authorize($request->user())) {
            return abort(403);
        }

        return $next($request);
    }
}