<?php

namespace Selene\Metrics;


use Selene\Resources\Resource;
use Illuminate\Http\Request;

abstract class ValueMetric extends Metric
{
    protected $type = 'value';
    protected $periods = [];

    function __construct($title)
    {
        parent::__construct($title);

        $this->periods = $this->periods();
    }

    function periods()
    {
        return [];
    }

    abstract function handle(Request $request, Resource $resource, $period, $extra);

    function handelRequest(Request $request, Resource $resource, $extra = null)
    {
        $periodKey = $request->get('period', false);

        if (isset($this->periods[$periodKey])) {
            $period = $periodKey;
        } else if (count($this->periods) > 0) {
            $period = array_keys($this->periods)[0];
        } else {
            $period = null;
        }

        return $this->handle($request, $resource, $period, $extra);
    }

    function export()
    {
        return parent::export() + [
                'periods' => $this->periods,
            ];
    }

    function result($value, $change = null)
    {
        return [
            'value' => $value,
            'change' => $change,
        ];
    }
}