<?php

namespace Selene\Types;


class HTMLText extends Type
{
    protected $type = 'html';

    protected $toolbars = null;

    /**
     * @return static
     */
    static function make($name, $column_name = null)
    {
        return (new static($name))->columnName(is_null($column_name) ? $name : $column_name);
    }

    function toolbars($toolbars)
    {
        $this->toolbars = call_user_func($toolbars);
        return $this;
    }

    function export()
    {
        return parent::export() + [
                'toolbars' => $this->toolbars,
            ];
    }
}