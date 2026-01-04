<?php

namespace wsydney76\blade\support;

use Craft;
use wsydney76\blade\View;
use wsydney76\blade\BladePlugin;

/**
 * Registers Blade conditionals (Laravel-style `View::if`).
 *
 * Built-ins provided:
 * - `@auth`  -> true when there is a logged-in Craft user
 * - `@guest` -> true when the current visitor is a guest
 *
 * Additional conditionals can be configured via `Settings::$bladeIfs`.
 */
class BladeIfs
{
    /**
     * Register all built-in conditionals.
     */
    public static function register(): void
    {
        View::if('auth', function (): bool {
            return !Craft::$app->user->getIsGuest();
        });

        View::if('guest', function (): bool {
            return Craft::$app->user->getIsGuest();
        });

        foreach (BladePlugin::getInstance()->getSettings()->bladeIfs as $name => $handler) {
            View::if($name, $handler);
        }
    }
}