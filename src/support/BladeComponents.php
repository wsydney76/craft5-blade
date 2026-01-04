<?php

namespace wsydney76\blade\support;

use wsydney76\blade\Blade;
use wsydney76\blade\BladePlugin;

/**
 * Registers class-based Blade components from plugin settings.
 *
 * This is a config-driven alternative to calling `Blade::component()` in module/plugin bootstrap.
 *
 * Config key: `Settings::$bladeComponents`
 */
class BladeComponents
{
    /**
     * Register class-based Blade components.
     *
     * Each config item should be an array with:
     * - `name`   (string) component alias, used as `<x-{name} />`
     * - `class`  (class-string) component class
     * - `prefix` (string|null) optional prefix, used as `<x-{prefix}-{name} />`
     */
    public static function register(): void
    {
        foreach (BladePlugin::getInstance()->getSettings()->bladeComponents as $name => $class) {
            Blade::component($name, $class);
        }
    }
}


