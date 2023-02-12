<?php

namespace Selene\Repositories;


use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Selene\Resources\Resource;
use Selene\Types\HasMany;
use Selene\Types\Relation;
use Selene\Types\Type;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ResourceModelRepository
{
    /** @var Resource */
    protected $resource;

    /** @var Builder */
    protected $query;

    /** @var Type[] */
    protected $fields = [];

    protected $searchable = [];

    /**
     * Repository constructor.
     * @param Resource $builder
     */
    public function __construct(Resource $resource, $query = null)
    {
        $this->resource = $resource;
        $this->query = is_null($query) ? $resource->getQuery() : $query;

        $this->searchable = $resource->getSearchable();
    }

    public static function make($resource, $query = null)
    {
        return new static($resource, $query);
    }

    function searchables(array $fields)
    {
        $this->searchable = $fields;
        return $this;
    }

    function searchFor($q)
    {
        $q = trim($q);

        if (count($this->searchable) > 0 && $q) {
            $this->query->where(function (Builder $query) use ($q) {
                foreach ($this->searchable as $item) {
                    $query->orWhere($item, 'LIKE', '%' . $q . '%');
                }
            });
        }

        return $this;
    }

    function fields($fields)
    {
        $this->fields = $fields;
        $this->query->select($this->getColNames($fields));
        return $this;
    }

    protected function getColNames($fields)
    {
        if (empty($fields)) {
            return '*';
        }

        $tableName = $this->query instanceof \Illuminate\Database\Eloquent\Relations\Relation ? $this->query->getQuery()->getQuery()->from : $this->query->getQuery()->from;

        return collect($fields)->reduce(function (Collection $carry, Type $type) {
            $c = $type instanceof Relation ? $this->extractRelationColumnName($type) : $type->getColumnName();

            if ($c === false)
                return $carry;

            $carry[] = $c;
            return $carry;
        }, collect($this->resource->getExtraColumns()))->unique()->map(function ($field) use ($tableName) {
            return strpos($field, '.') === false ? "{$tableName}.$field" : $field;
        })->unique()->all();
    }

    function filters($filters)
    {
        foreach ($this->fields as $field) {
            if ($field->isFilterable() && isset($filters[$field->getName()])) {
                $field->applyFilterToBuilder($this->query, $filters[$field->getName()]);
            }
        }

        return $this;
    }

    function sortBy($sort, $desc)
    {
        if ($sort) {
            foreach ($this->fields as $field) {
                if ($field->getName() == $sort && $field->isSortable()) {
                    $field->applySort($this->query, $desc);
                    return $this;
                }
            }
        }

        return $this;
    }

    /**
     * @return Builder
     */
    function getQuery()
    {
        return $this->query;
    }

    /**
     * @return Builder
     */
    function getQueryWithRelations()
    {
        $relations = [];

        foreach ($this->fields as $field) {
            if ($field instanceof Relation) {
                $rel = $field->getRelation();

                if ($rel) {
                    $relations[] = $rel;
                }
            }
        }

        return $this->query->with($relations);
    }

    protected function extractRelationColumnName(Relation $type)
    {
        $relation = call_user_func([$this->query->getModel(), $type->getRelation()]);

        if ($relation instanceof HasOne) {
            return $relation->getLocalKeyName();
        }

        if ($relation instanceof BelongsTo) {
            return $relation->getForeignKeyName();
        }

        if ($relation instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
            return $relation->getLocalKeyName();
        }

        if ($relation instanceof HasManyThrough) {
            return $relation->getLocalKeyName();
        }

        if ($relation instanceof BelongsToMany) {
            return $relation->getParentKeyName();
        }
    }
}
