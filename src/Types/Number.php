<?php

namespace Selene\Types;


class Number extends Type
{
    protected $type = 'number';
    protected $step = 1;
    protected $inheritedRules = ['numeric'];

    /**
     * @return static
     */
    static function make($name, $column_name = null)
    {
        return (new static($name))->columnName(is_null($column_name) ? $name : $column_name);
    }

    function step($step) {
        $this->step = $step;
        return $this;
    }

    function export()
    {
        return parent::export() + [
            'step' => $this->step
            ];
    }
}
