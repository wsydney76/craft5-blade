<?php

namespace wsydney76\blade\controllers;

use Craft;
use craft\web\Controller;
use wsydney76\blade\BladePlugin;

/**
 * Base Blade controller
 */
class BaseBladeController extends Controller
{
    public $blade;

    public function beforeAction($action): bool
    {
        $this->blade = BladePlugin::getInstance()->blade;

        return parent::beforeAction($action);
    }

    public function actionRender()
    {
        $template = Craft::$app->urlManager->getRouteParams()['blade_template'] ?? 'index';
        return $this->blade->render($template);
    }
}
