<?php

namespace Selene\Metrics;


use Selene;
use Selene\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;

class PaymentAmountSum extends ValueMetric
{

    public function periods()
    {
        return [
            'کل',
            'امروز',
            'هفته جاری',
            'ماه جاری',
            'سال جاری',
        ];
    }

    function handle(Request $request, Resource $resource, $period, $extra)
    {
        $periods = [
            [null, null],
            function () {
                return [
                    Jalalian::forge('00:00 today'),
                    Jalalian::forge('00:00 today')->subDays(1),
                ];
            },
            function () {
                return [
                    Jalalian::forge('last saturday 00:00'),
                    Jalalian::forge('last saturday 00:00')->subDays(7),
                ];
            },
            function () {
                $now = Jalalian::now();

                return [
                    Jalalian::fromFormat('Y-n-j', implode('-', [$now->getYear(), $now->getMonth(), 1])),
                    Jalalian::fromFormat('Y-n-j', implode('-', [$now->getYear(), $now->getMonth(), 1]))->subMonths(1),
                ];
            },
            function () {
                $now = Jalalian::now();

                return [
                    Jalalian::fromFormat('Y-n-j', implode('-', [$now->getYear(), 1, 1])),
                    Jalalian::fromFormat('Y-n-j', implode('-', [$now->getYear(), 1, 1]))->subYears(1),
                ];
            },
        ];

        $value = 0;
        $lastValue = null;

        /** @var Builder $query */
        $query = is_null($extra) ? $resource->getQuery() : $extra->payments();
        $query->succeeded();

        if (isset($periods[$period])) {
            $period = Selene::tap($periods[$period]);

            if ($period[0]) {
                if ($period[1]) {
                    $lastValue = clone $query;
                    $lastValue->where('payments.created_at', '>', $period[1]->toCarbon())
                        ->where('payments.created_at', '<', $period[0]->toCarbon());
                }

                $query->where('payments.created_at', '>', $period[0]->toCarbon());
            }
        }

        $value = $query->sum('amount');

        if ($lastValue) {
            $lastValue = $lastValue->sum('amount');
            if ($lastValue > 0) {
                $lastValue = round(($value - $lastValue) / $lastValue * 100, 2);
            }
        }

        return $this->result(number_format($value) . " ریال", $lastValue);
    }
}