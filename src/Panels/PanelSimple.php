<?php

namespace Selene\Panels;


class PanelSimple extends Panel
{
    protected $name;
    protected $support;
    protected $visible = true;

    function getType()
    {
        return 'simple';
    }

    function __construct($name = null, $support = true)
    {
        $this->name = $name;
        $this->support = $support;
    }

    public static function make($name = null, $support = true)
    {
        return new static($name, $support);
    }

    function defaultHidden() {
        $this->visible = false;
        return $this;
    }

    function export()
    {
        return [
                'name' => $this->name,
                'support' => $this->support,
                'visible' => $this->visible,
            ] + parent::export();
    }
}
