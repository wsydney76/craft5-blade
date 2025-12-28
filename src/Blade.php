<?php

namespace wsydney76\blade;

use Craft;
use craft\db\Paginator;
use craft\elements\db\ElementQueryInterface;
use craft\web\twig\variables\Paginate;

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
    public static function render(string|array $view, array $data = []): string
    {
        return self::instance()->render($view, $data);
    }

    /**
     * Render a Blade view using the current site's handle as a prefix.
     * Falls back to the unprefixed view if the prefixed one doesn't exist.
     */
    public static function renderLocalized(string $view, array $data = []): string
    {
        return self::instance()->render([
            Craft::$app->getSites()->getCurrentSite()->handle . '.' . $view,
            $view,
        ], $data);
    }

    /**
     * Proxy to Blade::directive().
     */
    public static function directive(string $name, callable $handler): void
    {
        self::instance()->directive($name, $handler);
    }

    /**
     * Register a custom Blade conditional (Blade::if equivalent).
     */
    public static function if(string $name, callable $handler): void
    {
        self::instance()->if($name, $handler);
    }

    /**
     * Proxy to Blade::share().
     */
    public static function share(string $key, mixed $value): void
    {
        self::instance()->share($key, $value);
    }

    /**
     * Register a custom stringable handler for a class.
     */
    public static function stringable(string $class, callable $handler): void
    {
        self::instance()->stringable($class, $handler);
    }

    /**
     * Create a paginator for the given query and return results and page info.
     *
     * @param ElementQueryInterface $query The element query to paginate
     * @param string $resultsKey The key name for results in the returned array
     * @param string $pageInfoKey The key name for page info in the returned array
     * @param array $config Configuration for the paginator
     * @return array<string, mixed> Returns array with results and pageInfo
     */
    public static function paginate(
        ElementQueryInterface $query,
        string $resultsKey = 'elements',
        string $pageInfoKey = 'pageInfo',
        array $config = []
    ): array {
        // Default page size to the query limit. Fall back to 100 if no limit set.
        $pageSize = $config['pageSize'] ?? ($query->limit ?? 100);

        // Remove limit from query for pagination count
        $query->limit(null);

        $paginator = new Paginator(
            query: $query,
            config: array_merge(
                [
                    'pageSize' => (int)$pageSize,
                    'currentPage' => (int)($config['currentPage'] ?? Craft::$app->request->getPageNum()),
                ],
                $config
            )
        );

        return [
            $resultsKey => collect($paginator->getPageResults()),
            $pageInfoKey => (new Paginate())->create($paginator),
        ];
    }
}

