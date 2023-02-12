<?php

namespace Selene\Resources;

use Illuminate\Database\Eloquent\Model;
use Selene\Panels\PanelSimple;
use Selene\Types\Type;
use Selene;
use Selene\Actions\Action;
use Selene\Metrics\Metric;
use Selene\Panels\Panel;

abstract class Resource
{
    public $model = 'App\Model';
    public $title = '';

    protected $visible = true;

    /** @var Type[] */
    protected $fields = [];
    /** @var Panel[] */
    protected $panels = [];

    protected $metrics = [];
    protected $actions = [];

    protected $search = true;
    protected $searchable = [];
    protected $primary_key = null;

    protected $singular = null;
    protected $plural = null;
    protected $createButtonText = null;

    protected $disableCreatePanel = false;
    protected $disableEditPanel = false;
    protected $disableDetailsPanel = false;
    protected $disableDeleteOption = false;

    protected $rearrange = false;
    protected $showIndexColumn = false;
    protected $defaultSort = null;
    protected $extraColumns = [];

    protected $index_help_tree = null;
    protected $details_help_tree = null;
    protected $create_help_tree = null;
    protected $edit_help_tree = null;

    function __construct()
    {
        $this->primary_key = (new $this->model)->getKeyName();
    }

    function getName()
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    function boot()
    {
        $this->panels = [];
        $this->fields = [];
        $this->metrics = [];
        $this->actions = [];

        $lastPanel = null;

        /** @var Type|Panel $field */
        foreach ($this->fields() as $field) {
            if ($field instanceof Panel) {
                if (!is_null($lastPanel)) {
                    $this->panels[] = $lastPanel;
                    $lastPanel = null;
                }

                $this->panels[] = $field;

                foreach ($field->getFields() as $f) {
                    $this->fields[$f->getName()] = $f;
                }
            } else if (is_a($field, Type::class)) {
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

        foreach ($this->fields as $field) {
            $field->setResource($this);
        }

        foreach ($this->metrics() as $metric) {
            $this->metrics[] = $metric;
        }

        foreach ($this->actions() as $action) {
            $this->actions[] = $action;
        }
    }

    public function isVisible(): bool
    {
        return Selene::tap($this->visible);
    }

    function fields()
    {
        return [

        ];
    }

    function getField($name)
    {
        return $this->fields[$name];
    }

    function metrics()
    {
        return [

        ];
    }

    function actions()
    {
        return [

        ];
    }

    function getAction($name)
    {
        return $this->actions[$name];
    }

    function getMetric($index)
    {
        return $this->metrics[$index];
    }

    function getSingular()
    {
        return $this->singular;
    }

    function getPlural()
    {
        return $this->plural;
    }

    function getSearchable()
    {
        return $this->searchable;
    }

    /**
     * @return null
     */
    public function getPrimaryKey()
    {
        return $this->primary_key;
    }

    /** @return Type[] */
    function visibleFieldsOnIndex()
    {
        return array_filter($this->fields, function (Type $field) {
            return $field->isVisibleOnIndex() || $field->getName() == $this->primary_key;
        });
    }

    /** @return Type[] */
    function visibleFieldsOnDetails()
    {
        return array_filter($this->fields, function (Type $field) {
            return $field->isVisibleOnDetails() || $field->getName() == $this->primary_key;
        });
    }

    /** @return Type[] */
    function visibleFieldsOnCreate()
    {
        return array_filter($this->fields, function (Type $field) {
            return $field->isVisibleWhenCreating() || $field->getName() == $this->primary_key;
        });
    }

    /** @return Type[] */
    function visibleFieldsOnEdit()
    {
        return array_filter($this->fields, function (Type $field) {
            return $field->isVisibleWhenEditing() || $field->getName() == $this->primary_key;
        });
    }

    function isCreatePanelDisabled(): bool
    {
        return $this->disableCreatePanel;
    }

    function isEditPanelDisabled(): bool
    {
        return $this->disableEditPanel;
    }

    function isDetailsPanelDisabled(): bool
    {
        return $this->disableDetailsPanel;
    }

    public function isDeleteOptionDisabled(): bool
    {
        return $this->disableDeleteOption;
    }

    function export()
    {
        $export = [
            'name' => $this->getName(),
            'visible' => $this->isVisible(),
            'singular' => $this->singular ?? str_singular($this->getName()),
            'plural' => $this->plural ?? str_plural($this->singular ?? str_singular($this->getName())),
            'create_text' => $this->createButtonText,
            'primary_key' => $this->primary_key,
            'details' => !$this->disableDetailsPanel,
            'create' => !$this->disableCreatePanel,
            'edit' => !$this->disableEditPanel,
            'remove' => !$this->disableDeleteOption,
            'index_column' => $this->showIndexColumn,
            'search' => $this->search,
            'index_help_link' => $this->index_help_tree ? Selene::helpTreeUrl($this->index_help_tree) : null,
            'details_help_link' => $this->details_help_tree ? Selene::helpTreeUrl($this->details_help_tree) : null,
            'create_help_link' => $this->create_help_tree ? Selene::helpTreeUrl($this->create_help_tree) : null,
            'edit_help_link' => $this->edit_help_tree ? Selene::helpTreeUrl($this->edit_help_tree) : null,
            'rearrange' => $this->isRearrangeable(),
        ];

        $export['panels'] = array_map(function (Panel $a) {
            return $a->export();
        }, $this->panels);

        $export['metrics'] = array_map(function (Metric $a) {
            return $a->export();
        }, $this->metrics);

        $export['actions'] = array_map(function (Action $a) {
            return $a->export();
        }, $this->actions);

        return $export;
    }

    function getQuery()
    {
        return call_user_func([$this->model, 'query']);
    }

    function getCreationRules()
    {
        return array_reduce($this->visibleFieldsOnCreate(), function ($carry, Type $item) {
            $carry = $carry + $item->getCreationRules();
            return $carry;
        }, []);
    }

    function getUpdateRules($model)
    {
        return array_reduce($this->visibleFieldsOnEdit(), function ($carry, Type $item) use ($model) {
            $carry = $carry + $item->getUpdateRules($model);
            return $carry;
        }, []);
    }

    function getRulesMessages()
    {
        return [];
    }

    function getRulesAttributes()
    {
        return array_reduce($this->fields, function ($carry, Type $item) {
            $carry = $carry + $item->getRulesAttributes();
            return $carry;
        }, []);
    }

    function fireCreating($request, $model)
    {
        $this->creating($request, $model);
        /** @todo: broadcast global event */
    }

    function fireCreated($request, $model)
    {
        $this->created($request, $model);
        /** @todo: broadcast global event */
    }

    function creating($request, $model)
    {
    }

    function created($request, $model)
    {
    }

    function fireUpdating($request, $model)
    {
        $this->updating($request, $model);
        /** @todo: broadcast global event */
    }

    function fireUpdated($request, $model)
    {
        $this->updated($request, $model);
        /** @todo: broadcast global event */
    }

    function updating($request, $model)
    {
    }

    function updated($request, $model)
    {
    }

    function fireDeleting($request, $model)
    {
        $this->deleting($request, $model);
        /** @todo: broadcast global event */
    }

    function fireDeleted($request, $model)
    {
        $this->deleted($request, $model);
        /** @todo: broadcast global event */
    }

    function deleting($request, $model)
    {
    }

    function deleted($request, $model)
    {
    }

    public function isRearrangeable()
    {
        return $this->rearrange !== false;
    }

    public function getRearrange()
    {
        return $this->rearrange;
    }

    public function getDefaultSort()
    {
        return $this->defaultSort ?? $this->getRearrange();
    }

    public function getExtraColumns()
    {
        return $this->extraColumns;
    }

    /** Authorization functions */

    function authorize($user): bool
    {
        return true;
    }

    function view(Model $model, $user): bool
    {
        return true;
    }

    function edit(Model $model, $user): bool
    {
        return true;
    }

    function delete(Model $model, $user): bool
    {
        return true;
    }
}
