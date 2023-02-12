<?php

namespace Selene\Types;


class Textarea extends Type
{
    protected $type = 'textarea';

    /**
     * @return static
     */
    static function make($name, $column_name = null)
    {
        return (new static($name))->columnName(is_null($column_name) ? $name : $column_name);
    }
}