<?php

namespace wsydney76\blade\support;

use Craft;
use craft\web\twig\Extension;
use wsydney76\blade\Blade;
use wsydney76\blade\BladePlugin;

/**
 * Shares Craft's Twig global variables with Blade templates.
 *
 * This makes the same globals you typically have in Twig (e.g. `craft`, `currentUser`,
 * `currentSite`, etc.) available as variables in Blade views.
 *
 * Additionally, any key/value pairs configured in `Settings::$bladeShared` are shared after
 * the Craft globals, so config values with the same key will override the Twig global.
 *
 * Implementation detail:
 * We instantiate Craft's Twig `Extension` and call `getGlobals()`. Some globals may be lazy
 * objects, but others can trigger service lookups. If you notice performance issues, consider
 * sharing a smaller subset (or sharing lazily via closures).
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

        foreach (BladePlugin::getInstance()->getSettings()->bladeShared as $key => $value) {
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