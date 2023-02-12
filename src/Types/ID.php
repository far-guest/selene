<?php

namespace Selene\Types;


class ID extends Type
{
    protected $type = 'number';

    function __construct($name)
    {
        parent::__construct($name);

        $this->visibleOnDetails = false;
        $this->visibleOnIndex = false;
        $this->visibleWhenCreating = false;
        $this->visibleWhenEditing = false;
    }

    /**
     * @return static
     */
    static function make()
    {
        return (new static('id'))->columnName('id');
    }
}