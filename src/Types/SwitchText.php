<?php

namespace Selene\Types;


class SwitchText extends Type
{
    protected $type = 'switch-text';

    protected $inheritedRules = ['nullable', 'string', 'max:255'];

    protected $default = '';

    /**
     * @return static
     */
    static function make($name, $column_name = null)
    {
        return (new static($name))->columnName(is_null($column_name) ? $name : $column_name);
    }

    function default($default)
    {
        $this->default = $default;
        return $this;
    }

    function export()
    {
        return parent::export() + [
                'default' => $this->default
            ];
    }
}