<?php

namespace wsydney76\blade\controllers;

use Craft;
use craft\web\Controller;
use wsydney76\blade\Blade;

/**
 * Base Blade controller
 */
class BaseBladeController extends Controller
{
    public function actionRender()
    {
        $template = Craft::$app->urlManager->getRouteParams()['blade_template'] ?? 'index';
        return Blade::render($template);
    }
}
