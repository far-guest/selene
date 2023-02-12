<?php


namespace Selene\Types;


use Illuminate\Database\Eloquent\Model;

abstract class Chart extends Type
{
    protected $type = 'chart';
    protected $columnName = false;

    protected $visibleWhenCreating = false;
    protected $visibleWhenEditing = false;
    protected $visibleOnIndex = false;

    abstract function getChartType();

    abstract function getChartOptions();

    abstract function getChartData(Model $model, $pivot = false);

    function extractValueFromModel(Model $model, $columnName = null, $pivot = false)
    {
        return $this->getChartData($model, $pivot);
    }

    public function export()
    {
        return parent::export() + [
                'chartType' => $this->getChartType(),
                'chartOptions' => $this->getChartOptions(),
            ];
    }
}