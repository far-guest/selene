<?php

namespace Selene\Types;


use Selene\Repositories\ResourceModelRepository;
use Selene\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\AssignOp\Mod;

class BelongsTo extends Relation
{
    protected $type = 'belongs_to';
    protected $join_when_sorting = true;

    protected $link_to_related = true;
    protected $filterable = true;
    protected $query = null;
    protected $dependencies = [];

    function __construct($name)
    {
        parent::__construct($name);

        $this->inheritedRules[] = function ($attribute, $value, $fail) {
            /** @todo this validation rule doesn't use custom query, check if $this->query is not empty and consume it */
            if (!is_null($value) && $this->getRelationResource()->getQuery()->whereKey($value)->doesntExist()) {
                $fail(trans('validation.exists', ['attribute' => $this->getRelationResource()->getSingular()]));
            }
        };

        $this->resolveUsing(function ($val, ?Model $model, $pivot) {
            return $this->resolveForRelatedModel($model->getRelation($this->getRelation()));
        })->displayUsing(function ($val, ?Model $model, $pivot) {
            return $this->displayForRelatedModel($model->getRelation($this->getRelation()));
        });
    }

    public function extractValueFromModel(Model $model, $columnName = null, $pivot = false)
    {
        return $this->displayForRelatedModel($model->getRelation($this->getRelation()));
    }

    function linkToRelated()
    {
        $this->link_to_related = true;
        return $this;
    }

    function withoutLinkToRelated()
    {
        $this->link_to_related = false;
        return $this;
    }

    protected function resolveForRelatedModel(?Model $model)
    {
        return $model ? $model->getKey() : null;
    }

    protected function displayForRelatedModel(?Model $model)
    {
        if (!$model) {
            return null;
        }

        if (is_string($this->getRelationResource()->title)) {
            $face = $model->getAttribute($this->getRelationResource()->title);
        } else if (is_callable($this->getRelationResource()->title)) {
            $face = call_user_func($this->getRelationResource()->title, $model);
        } else {
            $face = $model->getKey();
        }

        return [
            'id' => $model->getKey(),
            'title' => $face,
        ];
    }

    /**
     * @return static
     */
    static function make($relation, $resource)
    {
        return (new static($relation))
            ->relation($relation)
            ->relationResource($resource);
    }

    function getTitle()
    {
        return $this->title ?? $this->getRelationResource()->getSingular();
    }

    function fillFromRequest(Request $request, Model $model, \Closure $afterSaveTask)
    {
        call_user_func([$model, $this->relation])->associate($request->get($this->name));
    }

    function applyFilterToBuilder($query, $value)
    {
        parent::applyFilterToBuilder($query, $value);

        $values = array_filter($value, 'is_numeric');

        if (count($values) > 0) {
            $query->whereIn(call_user_func([$query->getModel(), $this->relation])->getForeignKeyName(), $values);
        }
    }

    public function handleRetrieveRequest(Request $request, Resource $resource, ?Model $model)
    {
        /** @var Resource $relationResource */
        $relationResource = $this->getRelationResource();

        if (!$relationResource->authorize($request->user()))
            return abort(403);

        if ($request->has('lookup')) {
            return $this->displayForRelatedModel($this->getRelationResource()->getQuery()->find($request->get('lookup')));
        }

        $query = ResourceModelRepository::make($this->getRelationResource())
            ->searchFor(trim($request->get('search')))
            ->getQuery();

        if (!is_null($this->query)) {
            $dependencies = $request->get('dependencies', []);
            $dependencies = array_map(function ($a) {
                return json_decode($a, true);
            }, is_array($dependencies) ? array_intersect_key($dependencies, array_flip($this->dependencies)) : []);
            if ($result = call_user_func_array($this->query, [$query, $model, $dependencies])) {
                $query = $result;
            }
        }

        $paginate = $query->paginate(10);

        $data = [];

        foreach ($paginate as $item) {
            $data[] = $this->displayForRelatedModel($item);
        }

        return [
            'data' => $data,
            'total' => $paginate->total(),
            'per_page' => $paginate->perPage(),
            'current_page' => $paginate->currentPage(),
        ];
    }

    function query($query)
    {
        $this->query = $query;
        return $this;
    }

    public function sortUsing($sorter, $join = true)
    {
        $this->join_when_sorting = $join;
        return parent::sortUsing($sorter);
    }

    public function isSortable(): bool
    {
        return $this->sortable && (is_string($this->getRelationResource()->title) || is_callable($this->sorter));
    }

    public function applySort($query, $desc = false)
    {
        if (is_callable($this->sorter)) {
            if ($this->join_when_sorting) {
                $this->joinWithRelated($query);
            }
            call_user_func_array($this->sorter, func_get_args());
        } else {
            $this->joinWithRelated($query);
            $query->orderBy($this->getRelationResource()->getQuery()->getModel()->getTable() . '.' . $this->getRelationResource()->title, $desc ? 'desc' : 'asc');
        }
    }

    /**
     * @param Builder $query
     */
    protected function joinWithRelated($query)
    {
        $hostModel = $query->getModel();
        $targetModel = $this->getRelationResource()->getQuery()->getModel();

        /** @var \Illuminate\Database\Eloquent\Relations\BelongsTo $relation */
        $relation = call_user_func([$hostModel, $this->getRelation()]);

        $query->leftJoin($targetModel->getTable(), $targetModel->getTable() . '.' . $relation->getOwnerKeyName(), '=', $hostModel->getTable() . '.' . $relation->getForeignKeyName());
    }

    function dependencies(...$dependencies)
    {
        $this->dependencies = $dependencies;
        return $this;
    }

    function export()
    {
        return [
                'link' => $this->link_to_related,
                'dependencies' => $this->dependencies,
            ] + parent::export();
    }
}
