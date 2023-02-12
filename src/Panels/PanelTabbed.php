<?php

namespace Selene\Panels;

use Selene\Types\Type;

class PanelTabbed extends Panel
{
    protected $assignsSeparateSpace = true;
    protected $name;

    /** @var Type */
    protected $fields = [];

    function getType()
    {
        return 'tabbed';
    }

    function __construct($name=null)
    {
        $this->name = $name;
    }

    public static function make($name=null)
    {
        return new static($name);
    }

    function export()
    {
        return [
                'name' => $this->name,
            ] + parent::export();
    }
}
