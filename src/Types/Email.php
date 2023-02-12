<?php

namespace Selene\Types;


class Email extends Type
{
    protected $type = 'email';

    /**
     * @return static
     */
    static function make($name, $column_name = null)
    {
        return (new static($name))->columnName(is_null($column_name) ? $name : $column_name);
    }
}