<?php

namespace wsydney76\blade\support;

use wsydney76\blade\Blade;
use wsydney76\blade\BladePlugin;

/**
 * Registers Blade view composers from plugin settings.
 *
 * This is a config-driven alternative to calling `Blade::composer()` from module/plugin bootstrap.
 *
 * Config key: `Settings::$bladeViewComposers`
 */
class BladeViewComposers
{
    /**
     * Register all configured view composers.
     *
     * Current settings shape:
     *
     * ```php
     * 'bladeViewComposers' => [
     *     'components.article.latest' => function ($view) {
     *         $view->with('latest', ...);
     *     }
     * ]
     * ```
     *
     * Where:
     * - key: `string|array<int,string>` view name(s) or patterns passed to `Blade::composer()`
     * - value: `\Closure|string` composer callback (same as `Blade::composer()`)
     */
    public static function register(): void
    {
        foreach (BladePlugin::getInstance()->getSettings()->bladeViewComposers as $view => $handler) {
            Blade::composer($view, $handler);
        }
    }
}
