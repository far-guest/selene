<?php


namespace Selene\Menu;

use Selene;
use Selene\Resources\Resource;

class AllResources extends GroupSimple
{
    function getLinks()
    {
        return array_map(function (Resource $resource) {
            return new ResourceLink($resource);
        }, array_filter(array_values(Selene::getResources()), function (Resource $resource) {
            return $resource->isVisible();
        }));
    }
}
