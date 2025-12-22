<?php

namespace wsydney76\blade;

use Craft;
use craft\web\twig\Extension;

class BladeShared {
    public static function register()
    {
        foreach (static::getGlobals() as $key => $value) {
            Blade::share($key, $value);
        }
    }

    /**
     * Return global variables from Craft's Twig environment.
     *
     * @return array
     */
    protected static function getGlobals(): array
    {
        $extension = new Extension(Craft::$app->getView(), Craft::$app->getView()->twig);
        return $extension->getGlobals();
    }

}