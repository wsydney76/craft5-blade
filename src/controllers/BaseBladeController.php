<?php

namespace wsydney76\blade\controllers;

use craft\web\Controller;
use wsydney76\blade\Blade;

/**
 * Base Blade controller
 */
class BaseBladeController extends Controller
{
    public function actionRender($view)
    {
        return Blade::render($view);
    }
}
