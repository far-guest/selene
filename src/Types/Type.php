<?php

namespace Selene\Types;

use Selene\Rules\RuleGenerator;
use Selene;
use Selene\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class Type
{
    /** @var Resource */
    protected $resource;

    protected $name;
    protected $title;
    protected $tooltip;
    protected $type;
    protected $help = false;
    protected $default = null;

    protected $columnName = null;

    protected $visibleOnIndex = true;
    protected $visibleOnDetails = true;
    protected $visibleWhenCreating = true;
    protected $visibleWhenEditing = true;

    protected $usesSeparatedSpace = false;

    protected $filterable = false;
    protected $sortable = true;
    protected $sorter = null;

    protected $presenter = null;
    protected $resolver = null;
    protected $storage = null;

    protected $rules = [];
    protected $updateRules = [];
    protected $creationRules = [];
    protected $inheritedRules = [];

    function __construct($name)
    {
        $this->name = $name;
    }

    function setResource(Resource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return Resource | null
     */
    public function getResource()
    {
        return $this->resource;
    }

    function name($name)
    {
        $this->name = $name;
        return $this;
    }

    function getName()
    {
        return $this->name;
    }

    function getTitle()
    {
        return $this->title ?? $this->name;
    }

    function getTooltip()
    {
        return $this->tooltip;
    }

    function getColumnName()
    {
        return $this->columnName;
    }

    function title($title)
    {
        $this->title = $title;
        return $this;
    }

    function tooltip($tooltip)
    {
        $this->tooltip = $tooltip;
        return $this;
    }

    function columnName($column_name)
    {
        $this->columnName = $column_name;
        return $this;
    }

    function help($help)
    {
        $this->help = $help;
        return $this;
    }

    function default($default)
    {
        $this->default = $default;
        return $this;
    }

    function rules(...$rules)
    {
        $this->rules = $rules;
        return $this;
    }

    function creationRules(...$rules)
    {
        $this->creationRules = $rules;
        return $this;
    }

    function updateRules(...$rules)
    {
        $this->updateRules = $rules;
        return $this;
    }

    function hideFromIndex()
    {
        $this->visibleOnIndex = false;
        return $this;
    }

    function hideFromDetail()
    {
        $this->visibleOnDetails = false;
        return $this;
    }

    function hideWhenCreating()
    {
        $this->visibleWhenCreating = false;
        return $this;
    }

    function hideWhenUpdating()
    {
        $this->visibleWhenEditing = false;
        return $this;
    }

    function onlyOnIndex()
    {
        $this->visibleOnIndex = true;
        return $this->hideFromDetail()->hideWhenCreating()->hideWhenUpdating();
    }

    function onlyOnDetail()
    {
        $this->visibleOnDetails = true;
        return $this->hideFromIndex()->hideWhenCreating()->hideWhenUpdating();
    }

    function onlyOnForms()
    {
        $this->visibleWhenCreating = true;
        $this->visibleWhenEditing = true;
        return $this->hideFromIndex()->hideFromDetail();
    }

    function exceptOnForms()
    {
        $this->visibleOnIndex = true;
        $this->visibleOnDetails = true;
        return $this->hideWhenCreating()->hideWhenUpdating();
    }

    function isVisibleOnIndex(): bool
    {
        return $this->visibleOnIndex;
    }

    function isVisibleOnDetails(): bool
    {
        return $this->visibleOnDetails;
    }

    function isVisibleWhenCreating(): bool
    {
        return $this->visibleWhenCreating;
    }

    function isVisibleWhenEditing(): bool
    {
        return $this->visibleWhenEditing;
    }

    public function usesSeparatedSpace(): bool
    {
        return $this->usesSeparatedSpace;
    }

    public function filterable($filterable = true)
    {
        $this->filterable = $filterable;
        return $this;
    }

    public function isFilterable(): bool
    {
        return $this->filterable;
    }

    public function sortable($sortable = true)
    {
        $this->sortable = $sortable;
        return $this;
    }

    public function isSortable(): bool
    {
        return $this->sortable && ($this->getColumnName() || is_callable($this->sorter));
    }

    public function sortUsing($sorter)
    {
        $this->sorter = $sorter;
        return $this;
    }

    /**
     * @param Builder $query
     * @param bool $desc
     * @return void
     */
    public function applySort($query, $desc = false)
    {
        if (is_callable($this->sorter)) {
            call_user_func_array($this->sorter, func_get_args());
        } else {
            $query->orderBy($this->getColumnName(), $desc ? 'desc' : 'asc');
        }
    }

    function resolveUsing($resolver)
    {
        $this->resolver = $resolver;
        return $this;
    }

    function displayUsing($presenter)
    {
        $this->presenter = $presenter;
        return $this;
    }

    function storeUsing($storage)
    {
        $this->storage = $storage;
        return $this;
    }

    function resolveFor(Model $model, $pivot = false)
    {
        if (is_null($this->resolver))
            return $this->extractValueFromModel($model, null, $pivot);

        if (is_string($this->resolver))
            return $this->extractValueFromModel($model, $this->resolver, $pivot);

        return call_user_func_array($this->resolver, [
            $this->extractValueFromModel($model, null, $pivot),
            $model,
            $pivot
        ]);
    }

    function displayFor(Model $model, $pivot = false)
    {
        if (is_null($this->presenter))
            return $this->resolveFor($model, $pivot);

        if (is_string($this->presenter))
            return $this->extractValueFromModel($model, $this->presenter, $pivot);

        return call_user_func_array($this->presenter, [
            $this->extractValueFromModel($model, null, $pivot),
            $model,
            $pivot
        ]);
    }

    function fillFromRequest(Request $request, Model $model, \Closure $afterSaveTask)
    {
        if (is_null($this->storage)) {
            $model->fill($this->extractValuesFromRequest($request, $model));
        } else {
            Selene::tap($this->storage, $request, $model, $afterSaveTask);
        }
    }

    function extractValuesFromRequest(Request $request, ?Model $model = null)
    {
        return $this->getColumnName() ? [
            $this->getColumnName() => $request->get($this->name),
        ] : [];
    }

    function extractValueFromModel(Model $model, $columnName = null, $pivot = false)
    {
        if (is_null($columnName)) {
            $columnName = $this->columnName;
        }

        return $pivot ? $model->pivot->getAttribute($columnName) : $model->getAttribute($columnName);
    }

    function applyFilterToBuilder($query, $value)
    {

    }

    public function handleRetrieveRequest(Request $request, Resource $resource, ?Model $model)
    {

    }

    public function handleActionRequest(Request $request, Resource $resource, Model $model)
    {

    }

    function export()
    {
        return [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'tooltip' => $this->getTooltip(),
            'help' => $this->help,
            'default' => $this->default,
            'type' => $this->type,
            'filterable' => $this->isFilterable(),
            'sortable' => $this->isSortable(),
            'isVisibleOnIndex' => $this->isVisibleOnIndex(),
            'isVisibleOnDetails' => $this->isVisibleOnDetails(),
            'isVisibleWhenCreating' => $this->isVisibleWhenCreating(),
            'isVisibleWhenEditing' => $this->isVisibleWhenEditing(),
        ];
    }

    function getCreationRules()
    {
        return [
            $this->getName() => array_map(function ($rule) {
                return $rule instanceof RuleGenerator ? $rule->generateCreationRule() : $rule;
            }, array_merge($this->inheritedRules, $this->rules, $this->creationRules))
        ];
    }

    function getUpdateRules(Model $model)
    {
        return [
            $this->getName() => array_map(function ($rule) use ($model) {
                return $rule instanceof RuleGenerator ? $rule->generateUpdateRule($model) : $rule;
            }, array_merge($this->inheritedRules, $this->rules, $this->updateRules))
        ];
    }

    public function getRulesAttributes()
    {
        return [
            $this->getName() => $this->getTitle(),
        ];
    }
}
