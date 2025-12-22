<?php

namespace wsydney76\blade;

use Craft;
use craft\web\twig\Extension;

/**
 * Shares Craft's Twig global variables with Blade templates.
 */
class BladeShared
{
    /**
     * Register and share all Craft global variables with Blade views.
     */
    public static function register(): void
    {
        foreach (static::getGlobals() as $key => $value) {
            Blade::share($key, $value);
        }
    }

    /**
     * Return global variables from Craft's Twig environment.
     *
     * @return array<string, mixed> Array of global variable names and values
     */
    protected static function getGlobals(): array
    {
        $extension = new Extension(Craft::$app->getView(), Craft::$app->getView()->twig);
        return $extension->getGlobals();
    }

}