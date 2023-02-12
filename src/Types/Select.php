<?php

namespace Selene\Types;


use Illuminate\Database\Eloquent\Model;

class Select extends Type
{
    protected $type = 'select';
    protected $multiple = false;

    protected $options = [];

    /**
     * @return static
     */
    static function make($name, $column_name = null)
    {
        return (new static($name))->columnName(is_null($column_name) ? $name : $column_name);
    }

    function options($options)
    {
        $this->options = call_user_func($options);
        return $this;
    }

    function multiple($multiple = true)
    {
        $this->multiple = $multiple;
        return $this;
    }

    function extractValueFromModel(Model $model, $columnName = null, $pivot = false)
    {
        $v = parent::extractValueFromModel($model, $columnName, $pivot);
        if ($this->multiple && !is_array($v)) {
            $v = json_decode($v);
        }
        return $v;
    }

    function export()
    {
        return parent::export() + [
                'options' => $this->options,
                'multiple' => $this->multiple,
            ];
    }
}