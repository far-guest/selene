<?php

namespace Selene\Types;


use Selene\Metrics\Metric;

trait HasMetrics
{
    protected $metrics = [];

    function metrics($metrics)
    {
        $this->metrics = call_user_func($metrics);
        return $this;
    }

    function getMetric($index): Metric
    {
        return $this->metrics[$index];
    }

    function exportMetrics()
    {
        return array_map(function (Metric $a) {
            return $a->export();
        }, $this->metrics);
    }
}