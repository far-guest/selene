<?php

namespace Selene\Middleware;

use Selene\Exceptions\NotRegisteredException;
use Selene\Resources\Resource;
use Closure;
use Illuminate\Http\Request;

class AccessResource
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $param = $request->route('selene_resource');

        try {
            /** @var Resource $resource */
            $resource = app('selene')->getResource($param);
        } catch (NotRegisteredException $e) {
            return abort(404);
        }

        if (!$resource->authorize($request->user())) {
            return abort(403);
        }

        return $next($request);
    }
}