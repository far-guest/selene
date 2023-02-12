<?php


namespace Selene\Menu;


use Selene;
use Selene\Views\View;

class ViewLink extends Link
{

    protected $view;
    /**
     * @var View|null
     */
    protected $temp = null;

    function __construct($class)
    {
        $this->view = $class;
    }

    public static function make($class)
    {
        return new static($class);
    }

    protected function getLinkType()
    {
        return 'view';
    }

    protected function getTitle()
    {
        return $this->getViewObject()->getTitle();
    }

    function export()
    {
        return parent::export() + [
                'view' => $this->getViewObject()->getName(),
            ];
    }

    /**
     * @return Resource
     */
    protected function getViewObject()
    {
        if (is_object($this->view)) {
            return $this->view;
        }

        if (is_null($this->temp)) {
            $this->temp = Selene::getView($this->view);
        }

        return $this->temp;
    }
}
