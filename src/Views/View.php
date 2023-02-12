<?php


namespace Selene\Views;


use Illuminate\Foundation\Auth\User as Authenticatable;

class View
{
    protected $title;
    protected $view;

    function getName()
    {
        return (new \ReflectionClass(static::class))->getShortName();
    }

    function getTitle()
    {
        return $this->title;
    }

    function mapping()
    {
        return [];
    }

    function render()
    {
        return view($this->view, $this->mapping())->render();
    }

    function authorize(Authenticatable $user)
    {
        return true;
    }

    function export()
    {
        return [
            'name' => $this->getName(),
            'title' => $this->title,
        ];
    }
}