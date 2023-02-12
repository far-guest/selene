<?php

namespace Selene\Types;


use Selene;
use Selene\Panels\Panel;
use Selene\Panels\PanelSimple;
use Selene\Rules\RuleGenerator;
use Selene\Repositories\ResourceModelRepository;
use Selene\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class BelongsToMany extends Relation
{
    use HasMetrics;

    protected $type = 'belongs_to_many';

    protected $visibleOnDetails = true;
    protected $visibleOnIndex = false;
    protected $visibleWhenCreating = false;
    protected $visibleWhenEditing = false;

    protected $usesSeparatedSpace = true;
    protected $inheritedRules = ['required'];

    /** @var Type[] */
    protected $fields = [];
    /** @var Panel[] */
    protected $panels = [];

    protected $disableAttachPanel = false;
    protected $disableEditPanel = false;
    protected $disableDetachOption = false;

    protected $showIndexColumn = false;

    protected $query = null;
    protected $candidatesQuery = null;
    protected $search = false;
    protected $searchables = null;

    protected $createButtonText;

    function __construct($name)
    {
        parent::__construct($name);
    }

    function disableAttachPanel()
    {
        $this->disableAttachPanel = true;
        return $this;
    }

    function disableEditPanel()
    {
        $this->disableEditPanel = true;
        return $this;
    }

    function disableDetachOption()
    {
        $this->disableDetachOption = true;
        return $this;
    }

    function withIndexColumn()
    {
        $this->showIndexColumn = true;
        return $this;
    }

    function query($query)
    {
        $this->query = $query;
        return $this;
    }

    function candidatesQuery($query)
    {
        $this->candidatesQuery = $query;
        return $this;
    }

    function noSearch()
    {
        $this->search = false;
        return $this;
    }

    function searchable(...$fields)
    {
        $this->searchables = $fields;
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

    function fields($fields)
    {
        $lastPanel = null;

        /** @var Type|Panel $field */
        foreach (call_user_func($fields) as $field) {
            if ($field instanceof Panel) {
                if (!is_null($lastPanel)) {
                    $this->panels[] = $lastPanel;
                    $lastPanel = null;
                }

                $this->panels[] = $field;

                foreach ($field->getFields() as $f) {
                    $this->fields[$f->getName()] = $f;
                }
            } else {
                if ($field->usesSeparatedSpace()) {
                    if (!is_null($lastPanel)) {
                        $this->panels[] = $lastPanel;
                        $lastPanel = null;
                    }

                    $this->panels[] = PanelSimple::make(null, false)->appendField($field);
                    $this->fields[$field->getName()] = $field;
                } else {
                    if (is_null($lastPanel)) {
                        $lastPanel = new PanelSimple();
                    }

                    $lastPanel->appendField($field);
                    $this->fields[$field->getName()] = $field;
                }
            }
        }

        if (!is_null($lastPanel)) {
            $this->panels[] = $lastPanel;
            $lastPanel = null;
        }

        return $this;
    }

    function getTitle()
    {
        return $this->title ?? $this->getRelationResource()->getPlural();
    }

    function getCreationRules()
    {
        return [
            '_' => array_map(function ($rule) {
                return $rule instanceof RuleGenerator ? $rule->generateCreationRule() : $rule;
            }, array_merge($this->inheritedRules, $this->rules, $this->creationRules))
        ];
    }

    function getUpdateRules(Model $model)
    {
        return [
            '_' => array_map(function ($rule) use ($model) {
                return $rule instanceof RuleGenerator ? $rule->generateUpdateRule($model) : $rule;
            }, array_merge($this->inheritedRules, $this->rules, $this->updateRules))
        ];
    }

    function getRulesAttributes()
    {
        return [
            '_' => $this->getRelationResource()->getSingular(),
        ];
    }

    function fillFromRequest(Request $request, Model $model, \Closure $afterSaveTask)
    {
    }

    public function handleRetrieveRequest(Request $request, Resource $resource, ?Model $model)
    {
        if ($request->get('action') == 'index') {
            return $this->actionIndex($request, $resource, $model);
        }

        if ($request->get('action') == 'metric') {
            return $this->actionMetric($request, $resource, $model, $request->get('metric'));
        }

        switch ($request->get('field')) {
            case'_':
            {
                if ($request->has('lookup')) {
                    return $this->displayCandidate(call_user_func([$model, $this->relation])->findOrFail($request->get('lookup')));
                }
                return $this->retrieveCandidates($request, $resource, $model);
            }
            default:
            {
                return $this->fields[$request->get('field')]->handleRetrieveRequest($request, $resource, $model);
            }
        }
    }


    public function handleActionRequest(Request $request, Resource $resource, Model $model)
    {
        if (!$this->disableAttachPanel && $request->method() == 'POST' && $request->get('action') == 'attach') {
            return $this->actionAttach($request, $resource, $model);
        }

        if (!$this->disableEditPanel && $request->method() == 'GET' && $request->get('action') == 'edit') {
            return $this->actionEdit($request, $resource, $model);
        }

        if (!$this->disableAttachPanel && $request->method() == 'POST' && $request->get('action') == 'update') {
            return $this->actionUpdate($request, $resource, $model);
        }

        if (!$this->disableDetachOption && $request->method() == 'POST' && $request->get('action') == 'detach') {
            return $this->actionDetach($request, $resource, $model);
        }

        return null;
    }

    function resolveFor(Model $model, $pivot = false)
    {
        return null;
    }

    function displayFor(Model $model, $pivot = false)
    {
        return null;
    }

    function createButtonText($text)
    {
        $this->createButtonText = $text;
        return $this;
    }

    function export()
    {
        return parent::export() + [
                'primaryKey' => $this->getRelationResource()->getPrimaryKey(),
                'panels' => array_map(function (Panel $a) {
                    return $a->export();
                }, $this->panels),
                'metrics' => $this->exportMetrics(),
                'attach' => !$this->disableAttachPanel,
                'edit' => !$this->disableEditPanel,
                'detach' => !$this->disableDetachOption,
                'index_column' => $this->showIndexColumn,
                'search' => $this->search,
                'create_text' => $this->createButtonText,
            ];
    }


    private function actionIndex(Request $request, Resource $resource, Model $model)
    {
        $relation = $this->getRelationResource();

        $fields = array_filter($this->fields, function (Type $field) use ($relation) {
            return $field->isVisibleOnIndex() || $field->getName() == $relation->getPrimaryKey();
        });

        $query = call_user_func([$model, $this->getRelation()]);

        if ($this->query) {
            if ($result = call_user_func_array($this->query, [$query, $model])) {
                $query = $result;
            }
        }

        $paginate = ResourceModelRepository::make($relation, $query)
            ->fields($fields)
            ->filters(json_decode($request->get('filters', '[]'), true));

        if ($this->searchables) {
            $paginate->searchables($this->searchables);
        }

        $paginate = $paginate
            ->searchFor(trim($request->get('search')))
            ->sortBy(trim($request->get('sort')), $request->has('desc'))
            ->getQueryWithRelations()
            ->paginate(20);

        $data = [];

        foreach ($paginate as $item) {
            $row = [];

            foreach ($fields as $field) {
                $row[$field->getName()] = $field->displayFor($item, $field->isVisibleWhenCreating() || $field->isVisibleWhenEditing());
            }

            $data[] = $row;
        }

        return [
            'data' => $data,
            'total' => $paginate->total(),
            'per_page' => $paginate->perPage(),
            'current_page' => $paginate->currentPage(),
        ];
    }

    private function actionAttach(Request $request, Resource $resource, Model $model)
    {
        $fields = array_filter($this->fields, function (Type $type) {
            return $type->isVisibleWhenCreating();
        });

        $rules = array_reduce($fields, function ($carry, Type $item) {
            $carry = $carry + $item->getCreationRules();
            return $carry;
        }, []);

        $messages = [];

        $attributes = array_reduce($fields, function ($carry, Type $item) {
            $carry = $carry + $item->getRulesAttributes();
            return $carry;
        }, []);

        $rules = $rules + $this->getCreationRules();
        $rules['_'][] = $this->getValidateCandidateRule($model);
        $attributes = $attributes + $this->getRulesAttributes();
        $data = $request->validate($rules, $messages, $attributes);

        $pivotData = [];

        foreach ($fields as $field) {
            $pivotData = $pivotData + $field->extractValuesFromRequest($request, null);
        }

        call_user_func([$model, $this->relation])->attach($data['_'], $pivotData);

        return response()->json(true);
    }

    private function actionEdit(Request $request, Resource $resource, Model $model)
    {
        $fields = array_filter($this->fields, function (Type $type) {
            return $type->isVisibleWhenEditing();
        });

        $target = call_user_func([$model, $this->relation])->findOrFail($request->get('item'));

        $data = [
            '_' => $target->getKey(),
        ];

        foreach ($fields as $field) {
            $data[$field->getName()] = $field->resolveFor($target, true);
        }

        return response()->json($data);
    }

    private function actionUpdate(Request $request, Resource $resource, Model $model)
    {
        $fields = array_filter($this->fields, function (Type $type) {
            return $type->isVisibleWhenEditing();
        });

        $target = call_user_func([$model, $this->relation])->findOrFail($request->get('item'));

        $rules = array_reduce($fields, function ($carry, Type $item) use ($target) {
            $carry = $carry + $item->getUpdateRules($target);
            return $carry;
        }, []);

        $messages = [];

        $attributes = array_reduce($fields, function ($carry, Type $item) {
            $carry = $carry + $item->getRulesAttributes();
            return $carry;
        }, []);

        $rules = $rules + $this->getUpdateRules($target);
        $rules['_'][] = $this->getValidateCandidateRule($model);
        $attributes = $attributes + $this->getRulesAttributes();
        $data = $request->validate($rules, $messages, $attributes);

        $pivotData = [];

        foreach ($fields as $field) {
            $pivotData = $pivotData + $field->extractValuesFromRequest($request, null);
        }

        call_user_func([$model, $this->relation])->updateExistingPivot($data['_'], $pivotData);

        return response()->json(true);
    }

    private function actionDetach(Request $request, Resource $resource, Model $model)
    {
        call_user_func([$model, $this->relation])->detach($request->get('item'));
        return response()->json(true);
    }

    private function retrieveCandidates(Request $request, Resource $resource, Model $model)
    {
        /** @var Resource $relationResource */
        $relationResource = $this->getRelationResource();

        if (!$relationResource->authorize($request->user()))
            return abort(403);

        $query = $this->getRelationResource()->getQuery();

        if ($this->candidatesQuery) {
            if ($result = call_user_func_array($this->candidatesQuery, [$query, $model])) {
                $query = $result;
            }
        }

        $repository = ResourceModelRepository::make($this->getRelationResource(), $query);

        if ($this->searchables) {
            $repository->searchables($this->searchables);
        }

        $query = $repository
            ->searchFor($request->get('search'))
            ->getQuery()
            ->whereNotIn('id', call_user_func([$model, $this->getRelation()])->allRelatedIds());

        $paginate = $query->paginate(10);

        $data = [];

        foreach ($paginate as $item) {
            $data[] = $this->displayCandidate($item);
        }

        return [
            'data' => $data,
            'total' => $paginate->total(),
            'per_page' => $paginate->perPage(),
            'current_page' => $paginate->currentPage(),
        ];
    }

    private function displayCandidate($model)
    {
        $targetModel = get_class($this->getRelationResource()->getQuery()->getModel());
        if (!($model instanceof $targetModel)) {
            $model = $model->getRelation($this->relation);
        }

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

    private function actionMetric($request, $resource, $model, $metric)
    {
        return $this->getMetric($metric)->handelRequest($request, $resource, $model);
    }

    private function getValidateCandidateRule(Model $model)
    {
        return function ($attribute, $value, $fail) use ($model) {
            if (!is_null($value)) {
                $query = $this->getRelationResource()->getQuery();

                if ($this->candidatesQuery) {
                    if ($result = call_user_func_array($this->candidatesQuery, [$query, $model])) {
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
