<?php


namespace Selene\Menu;


use Selene;
use Selene\Resources\Resource;

class ResourceLink extends Link
{
    protected $title = null;
    protected $resource;
    protected $page = 'index';
    protected $model = null;
    /**
     * @var Resource|null
     */
    protected $temp = null;

    function __construct($class, $title = null)
    {
        $this->resource = $class;
        $this->title = $title;
    }

    public static function make($class, $title = null)
    {
        return new static($class);
    }

    protected function getLinkType()
    {
        return 'resource';
    }

    protected function getTitle()
    {
        return $this->title ?? $this->getResourceObject()->getPlural();
    }

    function toCreate()
    {
        $this->page = 'create';
        return $this;
    }

    function toEdit($id)
    {
        $this->page = 'edit';
        $this->model = $id;
        return $this;
    }

    function toDetails($id)
    {
        $this->page = 'details';
        $this->model = $id;
        return $this;
    }

    function export()
    {
        return parent::export() + [
                'resource' => $this->getResourceObject()->getName(),
                'page' => $this->page,
                'model' => is_callable($this->model) ? call_user_func($this->model) : $this->model,
            ];
    }

    /**
     * @return Resource
     */
    protected function getResourceObject()
    {
        if (is_object($this->resource)) {
            return $this->resource;
        }

        if (is_null($this->temp)) {
            $this->temp = Selene::getResource($this->resource);
        }

        return $this->temp;
    }
}
