<?php

namespace wsydney76\blade\support;

use Craft;
use wsydney76\blade\Blade;

/**
 * Registers Blade conditionals (Laravel-style `Blade::if`).
 *
 * Provides:
 * - `@auth`  -> true when there is a logged-in Craft user
 * - `@guest` -> true when the current visitor is a guest
 */
class BladeIfs
{
    /**
     * Register all built-in conditionals.
     */
    public static function register(): void
    {
        Blade::if('auth', function (): bool {
            return !Craft::$app->user->getIsGuest();
        });

        Blade::if('guest', function (): bool {
            return Craft::$app->user->getIsGuest();
        });
    }
}