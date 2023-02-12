<?php

namespace Selene\Types;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;

class View extends Type
{
    protected $type = 'view';

    protected $visibleOnIndex = false;
    protected $visibleWhenEditing = false;
    protected $visibleWhenCreating = false;

    protected $view;
    protected $mapping;
    protected $columnName = false;

    /**
     * @return static
     */
    static function make($name, $view)
    {
        return (new static($name))->view($view);
    }

    function view($view)
    {
        $this->view = $view;
        return $this;
    }

    function mapping($mapping)
    {
        $this->mapping = $mapping;
        return $this;
    }

    function fillFromRequest(Request $request, Model $model, \Closure $afterSaveTask)
    {

    }

    function extractValuesFromRequest(Request $request, ?Model $model = null)
    {

    }

    function extractValueFromModel(Model $model, $columnName = null, $pivot = false)
    {
        return view($this->view, array_merge(
            ['model' => $model],
            $this->mapping ? call_user_func($this->mapping, $model) : []
        ))->render();
    }
}