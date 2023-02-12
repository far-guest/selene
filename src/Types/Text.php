<?php

namespace Selene\Types;


class Text extends Type
{
    protected $type = 'text';

    protected $inheritedRules = ['string', 'max:255'];

    /**
     * @return static
     */
    static function make($name, $column_name = null)
    {
        return (new static($name))->columnName(is_null($column_name) ? $name : $column_name);
    }
}