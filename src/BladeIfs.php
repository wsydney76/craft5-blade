<?php

namespace wsydney76\blade;

use Craft;

class BladeIfs
{
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