<?php

namespace wsydney76\blade;

use Craft;
use craft\db\Paginator;
use craft\elements\db\ElementQueryInterface;
use craft\web\twig\variables\Paginate;

/**
 * Static facade that reuses the plugin's underlying `BladeBootstrap` instance.
 *
 * This is the primary "public API" that Blade templates (and your PHP code) should call.
 * It provides a stable place to:
 * - Render views (`render`, `renderLocalized`)
 * - Register directives/conditionals/components
 * - Register Laravel-style view composers/creators
 */
class Blade
{
    /**
     * Cached singleton instance resolved from `BladePlugin::getInstance()->blade`.
     */
    private static ?BladeBootstrap $blade = null;

    /**
     * Resolve the plugin's Blade instance (lazy, cached).
     *
     * @return BladeBootstrap
     */
    private static function instance(): BladeBootstrap
    {
        if (self::$blade === null) {
            self::$blade = BladePlugin::getInstance()->blade;
        }

        return self::$blade;
    }

    /**
     * Render a Blade view.
     *
     * View name format:
     * - Dotted: `blog.entry` -> `resources/views/blog/entry.blade.php`
     * - Array of candidates: the first existing view is rendered (Laravel-style `first()`)
     *
     * @param string|array<int, string> $view
     * @param array<string, mixed> $data
     */
    public static function render(string|array $view, array $data = []): string
    {
        return self::instance()->render($view, $data);
    }

    /**
     * Render a Blade view using the current site's handle as a prefix.
     *
     * Example (current site handle `en`):
     * - tries `en.home`
     * - falls back to `home`
     *
     * @param string $view Unprefixed view name, e.g. `home`
     * @param array<string, mixed> $data
     */
    public static function renderLocalized(string $view, array $data = []): string
    {
        return self::instance()->render([
            Craft::$app->getSites()->getCurrentSite()->handle . '.' . $view,
            $view,
        ], $data);
    }

    /**
     * Register a custom Blade directive.
     *
     * The handler receives the raw expression string from the template (including parentheses).
     */
    public static function directive(string $name, callable $handler): void
    {
        self::instance()->directive($name, $handler);
    }

    /**
     * Register a custom Blade conditional (equivalent to Laravel's `Blade::if`).
     *
     * Creates helper directives like `@auth ... @endauth` or `@guest`.
     */
    public static function if(string $name, callable $handler): void
    {
        self::instance()->if($name, $handler);
    }

    /**
     * Share a variable with all Blade views.
     */
    public static function share(string $key, mixed $value): void
    {
        self::instance()->share($key, $value);
    }

    /**
     * Register a custom stringable handler for a class.
     *
     * Used by Blade/Laravel when casting objects to strings in templates.
     *
     * @param class-string $class
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

    /**
     * Register a view composer (Laravel-style).
     *
     * Composers run when a view is being rendered and can attach additional data.
     *
     * @param string|array<int, string> $views View name(s) or patterns (e.g. '*', 'site.home')
     * @param \Closure|string $callback Closure(View $view) or class@method
     */
    public static function composer(string|array $views, \Closure|string $callback): void
    {
        self::instance()->factory()->composer($views, $callback);
    }

    /**
     * Register a view creator (runs before the view is rendered; similar to composer).
     *
     * @param string|array<int, string> $views
     * @param \Closure|string $callback
     */
    public static function creator(string|array $views, \Closure|string $callback): void
    {
        self::instance()->factory()->creator($views, $callback);
    }

    /**
     * Register a class-based Blade component.
     *
     * @param string $name Component alias used in templates.
     * @param class-string $class Backing class that renders the component.
     * @param string|null $prefix Optional prefix (e.g. `ui` for `<x-ui-alert />`).
     */
    public static function component(string $name, string $class, ?string $prefix = null): void
    {
        self::instance()->component($name, $class, $prefix);
    }
}
