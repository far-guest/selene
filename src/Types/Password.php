<?php

namespace Selene\Types;


use Illuminate\Database\Eloquent\Model;

class Password extends Type
{
    protected $type = 'password';

    protected $inheritedRules = ['string', 'confirmed'];

    protected $visibleOnIndex = false;
    protected $visibleOnDetails = false;

    /**
     * @return static
     */
    static function make($name, $column_name = null)
    {
        return (new static($name))->columnName(is_null($column_name) ? $name : $column_name);
    }

    function displayFor(Model $model, $pivot = false)
    {
        return '';
    }

    function resolveFor(Model $model, $pivot = false)
    {
        return '';
    }
}