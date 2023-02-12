<?php

namespace Selene\Exceptions;


class NotRegisteredException extends \Exception
{
    public function __construct($class)
    {
        parent::__construct((new \ReflectionClass($class))->getName() . " is not registered.");
    }
}