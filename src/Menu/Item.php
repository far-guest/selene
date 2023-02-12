<?php


namespace Selene\Menu;


abstract class Item
{
    abstract protected function getC();

    function export()
    {
        return [
            'c' => $this->getC(),
        ];
    }
}
