<?php

namespace wsydney76\blade\support;

use wsydney76\blade\View;
use wsydney76\blade\BladePlugin;

/**
 * Registers Blade "stringable" handlers (Laravel-style `View::stringable`).
 *
 * Stringables allow you to customize how objects are converted to strings when they are
 * echoed in Blade templates.
 *
 * Config-driven via `Settings::$bladeStringables`.
 */
class BladeStringables
{
    /**
     * Register all configured stringable handlers.
     *
     * @see \Illuminate\View\Compilers\BladeCompiler::stringable()
     */
    public static function register(): void
    {
        foreach (BladePlugin::getInstance()->getSettings()->bladeStringables as $class => $handler) {
            View::stringable($class, $handler);
        }
    }
}