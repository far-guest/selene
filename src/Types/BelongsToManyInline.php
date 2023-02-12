<?php

namespace Selene\Types;


use Selene;
use Selene\Repositories\ResourceModelRepository;
use Selene\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class BelongsToManyInline extends Relation
{
    protected $type = 'belongs_to_many_inline';

    protected $visibleOnDetails = true;
    protected $visibleOnIndex = true;
    protected $visibleWhenCreating = true;
    protected $visibleWhenEditing = true;

    protected $query = null;
    protected $searchables = null;
    protected $dependencies = [];

    function __construct($name)
    {
        parent::__construct($name);

        $this->inheritedRules[] = function ($attribute, $value, $fail) {
            /** @todo this validation rule doesn't use custom query, check if $this->query is not empty and consume it */

            if (is_array($value)) {
                $ids = array_map(function ($a) {
                    return $a['id'];
                }, $value);

                if ($this->getRelationResource()->getQuery()->whereIn('id', $ids)->count() != count($ids)) {
                    $fail(trans('validation.exists', ['attribute' => $this->getRelationResource()->getSingular()]));
                }
            }
        };
    }

    function query($query)
    {
        $this->query = $query;
        return $this;
    }

    function searchable(...$fields)
    {
        $this->searchables = $fields;
        return $this;
    }

    function dependencies(...$dependencies)
    {
        $this->dependencies = $dependencies;
        return $this;
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
        return $this->title ?? $this->getRelationResource()->getPlural();
    }

    function fillFromRequest(Request $request, Model $model, \Closure $afterSaveTask)
    {
        if (is_null($this->storage)) {
            $value = $request->get($this->name, []);

            if (is_array($value)) {
                $ids = array_map(function ($a) {
                    return $a['id'];
                }, $value);

                $afterSaveTask(function (Request $request, Model $model) use ($ids) {
                    call_user_func([$model, $this->getRelation()])->sync($ids);
                });
            } else {
                $afterSaveTask(function (Request $request, Model $model) {
                    call_user_func([$model, $this->getRelation()])->sync([]);
                });
            }
        } else {
            Selene::tap($this->storage, $request, $model);
        }
    }

    public function handleRetrieveRequest(Request $request, Resource $resource, ?Model $model)
    {
        /** @var Resource $relationResource */
        $relationResource = $this->getRelationResource();

        if (!$relationResource->authorize($request->user()))
            return abort(403);

        $query = ResourceModelRepository::make($this->getRelationResource());

        if ($this->searchables) {
            $query->searchables($this->searchables);
        }

        $query = $query
            ->searchFor($request->get('search'))
            ->getQuery();

        if ($this->query) {
            $dependencies = $request->get('dependencies', []);
            $dependencies = array_map(function ($a) {
                return json_decode($a, true);
            }, is_array($dependencies) ? array_intersect_key($dependencies, array_flip($this->dependencies)) : []);
            if ($result = call_user_func_array($this->query, [$query, $model, $dependencies])) {
                $query = $result;
            }
        }

        $paginate = $query->get();

        $data = [];

        foreach ($paginate as $item) {
            $data[] = $this->displayCandidate($item);
        }

        return $data;
    }

    function resolveFor(Model $model, $pivot = false)
    {
        /** @todo implement pivot mode */
        return call_user_func([$model, $this->getRelation()])->get()->map(function ($model) {
            return $this->displayCandidate($model);
        })->all();
    }

    function displayFor(Model $model, $pivot = false)
    {
        /** @todo implement pivot mode */
        return call_user_func([$model, $this->getRelation()])->get()->map(function ($model) {
            return $this->displayCandidate($model);
        })->all();
    }

    function export()
    {
        return parent::export() + [
                'dependencies' => $this->dependencies,
            ];
    }

    private function displayCandidate($model)
    {
        if (is_null($this->presenter)) {
            if (is_string($this->getRelationResource()->title)) {
                $this->presenter = $this->getRelationResource()->title;
            } else if (is_callable($this->getRelationResource()->title)) {
                $this->presenter = function ($value, $model, $pivot) {
                    return call_user_func($this->getRelationResource()->title, $model);
                };
            }
        }

        return [
            'id' => $model ? $model->getKey() : null,
            'title' => $model ? parent::displayFor($model) : null
        ];
    }

    private function getValidateCandidateRule(Model $model)
    {
        return function ($attribute, $value, $fail) use ($model) {
            dd($attribute, $value);
            if (!is_null($value)) {
                $query = $this->getRelationResource()->getQuery();

                if ($this->query) {
                    if ($result = call_user_func_array($this->query, [$query, $model])) {
                        $query = $result;
                    }
                }

                if ($query->whereKey($value)->doesntExist()) {
                    $fail(trans('validation.exists', ['attribute' => $this->getRelationResource()->getSingular()]));
                }
            }
        };
    }
}