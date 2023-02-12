<?php

namespace Selene\Types;


use Illuminate\Database\Eloquent\Model;

class Boolean extends Type
{
    protected $type = 'boolean';

    protected $inheritedRules = ['boolean'];

    /**
     * @return static
     */
    static function make($name, $column_name = null)
    {
        return (new static($name))->columnName(is_null($column_name) ? $name : $column_name);
    }

    function resolveFor(Model $model, $pivot = false)
    {
        return parent::resolveFor($model, $pivot) ? true : false;
    }

    function displayFor(Model $model, $pivot = false)
    {
        return parent::displayFor($model, $pivot) ? true : false;
    }
}