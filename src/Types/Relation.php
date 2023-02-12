<?php

namespace Selene\Types;


use Illuminate\Database\Eloquent\Builder;
use Selene\Resources\Resource;

abstract class Relation extends Type
{
    protected $relation = null;
    protected $relation_resource = null;
    protected $columnName = false;

    function getRelation()
    {
        return $this->relation;
    }

    /**
     * @return Resource
     */
    function getRelationResource()
    {
        return app('selene')->getResource($this->relation_resource);
    }

    function relation($relation)
    {
        $this->relation = $relation;
        return $this;
    }

    function relationResource($resource)
    {
        $this->relation_resource = $resource;
        return $this;
    }

    function export()
    {
        $resource = $this->getRelationResource();

        return [
                'relation' => $this->relation,
                'relation_resource' => (new \ReflectionClass($resource))->getShortName(),
            ] + parent::export();
    }

}