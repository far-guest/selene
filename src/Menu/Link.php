<?php


namespace Selene\Menu;


abstract class Link extends Item
{

    abstract protected function getLinkType();

    abstract protected function getTitle();

    protected function getC()
    {
        return 'link';
    }

    function export()
    {
        return parent::export() + [
                'type' => $this->getLinkType(),
                'title' => $this->getTitle(),
            ];
    }
}