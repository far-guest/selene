<?php

namespace Selene\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Selene;
use Selene\Views\View;

class SeleneViewController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    function render(Request $request, $view)
    {
        /** @var View $view */
        $view = selene::getView($view);
        return $view->render();
    }
}
