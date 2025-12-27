<?php

namespace wsydney76\blade\controllers;

use craft\web\Controller;
use wsydney76\blade\Blade;

/**
 * Base Blade controller
 */
class BaseBladeController extends Controller
{
    protected array|bool|int $allowAnonymous = true;

    /**
     * Render a Blade view and return the rendered output.
     */
    public function actionRender(string $view): string
    {
        return Blade::render($view);
    }
}
