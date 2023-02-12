<?php

namespace Selene\Metrics;


use Selene\Resources\Resource;
use Illuminate\Http\Request;

class SimpleValueMetric extends ValueMetric
{
    protected $handler;

    function handler($handler)
    {
        $this->handler = $handler;
        return $this;
    }

    function handle(Request $request, Resource $resource, $period, $extra)
    {
        return call_user_func_array($this->handler, [$this, $resource, $period, $extra]);
    }
}