<?php

namespace Selene\Metrics;

use Selene\Resources\Resource;
use Illuminate\Http\Request;

abstract class Metric
{
    protected $title;
    protected $type;

    function __construct($title)
    {
        $this->title = $title;
    }

    public static function make($title)
    {
        $class = static::class;
        return new $class($title);
    }

    function getTitle()
    {
        return $this->title;
    }

    abstract function handelRequest(Request $request, Resource $resource, $extra = null);

    function export()
    {
        return [
            'title' => $this->getTitle(),
            'type' => $this->type,
        ];
    }
}