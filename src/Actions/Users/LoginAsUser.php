<?php

namespace Selene\Actions\Users;


use Selene\Actions\Action;
use User;
use Illuminate\Support\Collection;

class LoginAsUser extends Action
{
    protected $title = 'ورود به پنل کاربری';

    public function handel(array $fields, Collection $models)
    {
        if (auth()->user()->can('ManageGlobally')) {
            auth()->login($models->first());
            return self::redirect(route('panel.index'));
        }

        return self::message('خطا', 'شما اجازه انجام این عملیات را ندارید', 'error');
    }
}