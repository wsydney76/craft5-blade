<?php

namespace wsydney76\blade;

/**
 * Static helper to reuse the plugin's Blade instance.
 */
class Blade
{
    private static ?BladeBootstrap $blade = null;

    /**
     * Lazily resolve the plugin's Blade instance once.
     */
    private static function instance(): BladeBootstrap
    {
        if (self::$blade === null) {
            self::$blade = BladePlugin::getInstance()->blade;
        }

        return self::$blade;
    }

    /**
     * Proxy to Blade::render().
     */
    public static function render(string $view, array $data = []): string
    {
        return self::instance()->render($view, $data);
    }

    /**
     * Proxy to Blade::directive().
     */
    public static function directive(string $name, callable $handler): void
    {
        self::instance()->directive($name, $handler);
    }

    /**
     * Proxy to Blade::share().
     */
    public static function share(string $key, mixed $value): void
    {
        self::instance()->share($key, $value);
    }
}

