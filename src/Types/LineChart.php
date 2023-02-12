<?php


namespace Selene\Types;

use Selene;
use Illuminate\Database\Eloquent\Model;

class LineChart extends Chart
{
    protected $x_extractor = null;
    protected $y_extractor = [];

    protected $colors = [
        '0,63,92',
        '88,80,141',
        '188,80,144',
        '255,99,97',
        '255,166,0',
    ];

    public static function make($name)
    {
        return (new static($name));
    }

    function getChartType()
    {
        return 'line';
    }

    function getChartOptions()
    {
        return [
            'scales' => [
                'yAxes' => [
                    [
                        'ticks' => [
                            'suggestedMin' => 0,
                        ]
                    ]
                ]
            ]
        ];
    }

    function getChartData(Model $model, $pivot = false)
    {
        return [
            'x' => $this->x_extractor ? Selene::cic($this->x_extractor, $model) : [],
            'ys' => array_map(function ($extractor, $i) use ($model) {
                return call_user_func_array($extractor, [
                    $model,
                    'rgba(' . $this->colors[$i % count($this->colors)] . ',0.7)',
                    'rgb(' . $this->colors[$i % count($this->colors)] . ')',
                ]);
            }, $this->y_extractor, array_keys($this->y_extractor)),
        ];
    }

    function x($extractor)
    {
        $this->x_extractor = $extractor;
        return $this;
    }

    function y($label, $extractor, $backgroundColor = null, $borderColor = null)
    {
        $this->y_extractor[] = function ($model, $dbgc, $dbc) use ($label, $extractor, $backgroundColor, $borderColor) {
            $border = $borderColor ?? ($backgroundColor ? null : $dbc);
            return [
                    'label' => $label,
                    'borderColor' => $borderColor ?? ($backgroundColor ? null : $dbc),
                    'backgroundColor' => $backgroundColor ?? $dbgc,
                    'data' => call_user_func($extractor, $model),
                    'fill' => false,
                ] + (is_null($border) ? [] : ['borderWidth' => 1]);
        };
        return $this;
    }
}