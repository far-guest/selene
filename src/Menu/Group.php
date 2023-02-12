<?php


namespace Selene\Menu;


abstract class Group extends Item
{
    abstract protected function getType();

    abstract protected function getTitle();

    abstract protected function getIcon();

    abstract protected function getLinks();

    protected function getC()
    {
        return 'group';
    }

    function export()
    {
        return parent::export() + [
                'type' => $this->getType(),
                'icon' => $this->getIcon(),
                'title' => $this->getTitle(),
                'links' => array_map(function (Link $link) {
                    return $link->export();
                }, $this->getLinks()),
            ];
    }
}