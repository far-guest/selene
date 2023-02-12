<?php


namespace Selene\Menu;


class GroupSimple extends Group
{
    protected $title;
    protected $icon;
    protected $links = [];

    public function __construct($title, $icon)
    {
        $this->title = $title;
        $this->icon = $icon;
    }

    public static function make($title, $icon)
    {
        return new static($title, $icon);
    }

    function links($links)
    {
        $this->links = $links;
        return $this;
    }

    protected function getType()
    {
        return 'flat';
    }

    protected function getTitle()
    {
        return $this->title;
    }

    protected function getIcon()
    {
        return $this->icon;
    }

    protected function getLinks()
    {
        return is_callable($this->links) ? call_user_func($this->links) : $this->links;
    }
}