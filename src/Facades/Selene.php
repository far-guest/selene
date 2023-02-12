<?php

namespace Selene\Facades;


use Illuminate\Support\Facades\Facade;

class Selene extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'selene';
    }
}