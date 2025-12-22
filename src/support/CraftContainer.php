<?php

namespace wsydney76\blade\support;

use Illuminate\Container\Container;

/**
 * Custom service container for Blade that extends Laravel's Container.
 *
 * Provides Laravel-specific functionality needed by Blade without requiring a full Laravel application.
 */
class CraftContainer extends Container
{
    /**
     * Return the application namespace used by Blade when resolving component classes.
     *
     * Default Laravel apps use 'App\\' as the root namespace.
     * Adjust if you want to resolve components from a different base.
     * This comes into play when using custom directives like @datetime or components like <x-...>
     *
     * Note: This is a monkey patch, avoiding handling a full Laravel application just for this.
     * The 'correct' way would be use a full Application class extending Illuminate\Foundation\Application
     *
     * composer require laravel/framework
     * use Illuminate\Foundation\Application;
     * $app = new Application(__DIR__);
     *
     * @return string The application namespace for component resolution
     */
    public function getNamespace(): string
    {

        return 'App\\';
    }
}

