<?php

namespace wsydney76\blade\controllers;

use Craft;
use craft\web\Controller;
use wsydney76\blade\BladeBootstrap;

/**
 * Base Blade controller
 */
class BaseBladeController extends Controller
{
    public $blade;

    public function beforeAction($action): bool
    {
        $this->blade = new BladeBootstrap(
            '/var/www/html/resources/views',
            '/var/www/html/storage/blade/cache'
        );

        $this->blade->share('systemName', Craft::$app->getSystemName());
        $this->blade->share('currentSite', Craft::$app->sites->getCurrentSite());
        $this->blade->share('currentUser', Craft::$app->user->getIdentity());

        return parent::beforeAction($action);
    }

    public function actionRender()
    {
        $template = Craft::$app->urlManager->getRouteParams()['template'] ?? 'index';
        return $this->blade->render($template);
    }
}
