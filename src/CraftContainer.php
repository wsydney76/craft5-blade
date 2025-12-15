<?php

namespace wsydney76\blade;

use Illuminate\Container\Container;

class CraftContainer extends Container
{
    /**
     * Return the application namespace used by Blade when resolving component classes.
     */
    public function getNamespace(): string
    {
        // Default Laravel apps use 'App\\' as the root namespace.
        // Adjust if you want to resolve components from a different base.
        return 'App\\';
    }
}

