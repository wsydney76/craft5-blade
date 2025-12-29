<?php

namespace wsydney76\blade\support;

use Illuminate\Container\Container;

/**
 * Custom service container for the Blade runtime.
 *
 * Blade (and some of its component/tag features) expect to run inside a Laravel application.
 * Craft isn't a Laravel app, so we provide a minimal container implementation that satisfies the
 * pieces Blade tries to call.
 *
 * In particular, some component resolution paths call `$app->getNamespace()`.
 */
class CraftContainer extends Container
{
    /**
     * Return the application namespace used by Blade when resolving component classes.
     *
     * Default Laravel apps use `App\\` as the root namespace.
     * Change this if your project stores component classes elsewhere.
     *
     * @return string Application namespace prefix for component resolution.
     */
    public function getNamespace(): string
    {
        return 'App\\';
    }
}
