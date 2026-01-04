<?php

namespace wsydney76\blade\support;

use wsydney76\blade\View;
use wsydney76\blade\BladePlugin;

/**
 * Registers Blade view composers from plugin settings.
 *
 * This is a config-driven alternative to calling `View::composer()` from module/plugin bootstrap.
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
     * - key: `string|array<int,string>` view name(s) or patterns passed to `View::composer()`
     * - value: `\Closure|string` composer callback (same as `View::composer()`)
     */
    public static function register(): void
    {
        foreach (BladePlugin::getInstance()->getSettings()->bladeViewComposers as $view => $handler) {
            View::composer($view, $handler);
        }
    }
}
