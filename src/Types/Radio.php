<?php

namespace Selene\Types;


class Radio extends Type
{
    protected $type = 'radio';

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

    function export()
    {
        return parent::export() + [
                'options' => $this->options,
            ];
    }
}