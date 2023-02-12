<?php

namespace Selene\Exceptions;


class NotRegistrableException extends \Exception
{
    public function __construct($class)
    {
        parent::__construct((new \ReflectionClass($class))->getName() . " is not a registrable item.");
    }
}