<?php

namespace Selene\Tools;

use Illuminate\Foundation\Auth\User as Authenticatable;


abstract class Tool
{
    protected $name;
    protected $title;
    protected $policy = null;

    function __construct($name)
    {
        $this->name = $name;
    }

    public static function make($name)
    {
        return new static($name);
    }

    function getName()
    {
        return $this->name;
    }

    function getTitle()
    {
        return $this->title;
    }

    function canSee($policy)
    {
        $this->policy = $policy;
        return $this;
    }

    function authorize(Authenticatable $user)
    {
        if (is_null($this->policy))
            return true;

        return call_user_func($this->policy, $user);
    }

    function registerRoutes()
    {

    }

    function export()
    {
        $reflect = new \ReflectionClass($this);

        return [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'type' => $reflect->getShortName(),
        ];
    }
}