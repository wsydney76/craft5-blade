<?php

namespace wsydney76\blade;

class BladeDirectives
{
    public static function register(): void
    {
        // Render Twig directive
        Blade::directive('renderTwig', function($expression) {
            return "<?php echo \\Craft::\$app->view->renderTemplate($expression); ?>";
        });

        // includeLocalized directive using extracted compiler method
        Blade::directive('includeLocalized', function($expression) {
            return static::compileIncludeLocalized($expression);
        });

        Blade::directive('set', function ($expression) {
            return "<?php {$expression}; ?>";
        });

        Blade::directive('paginate', function($expression) {
            return static::compilePaginate($expression);
        });
    }

    /**
     * Compiler for the includeLocalized directive.
     * Builds a PHP snippet that attempts site-specific template first, then falls back.
     */
    public static function compileIncludeLocalized(string $expression): string
    {
        // Wrap raw expression in array brackets to safely parse comma-separated args
        // $expression will look like: 'meta', ['entry' => $entry]
        return <<<PHP
<?php
    \$__args = [{$expression}];

    // Support both call styles: @includeLocalized('meta', [...]) and @includeLocalized(['meta', [...]])
    if (count(\$__args) === 1 && is_array(\$__args[0])) {
        \$__args = \$__args[0];
    }

    \$__template = \$__args[0] ?? null;
    \$__data = \$__args[1] ?? [];

    // Determine site handle from shared global (if present) or Craft as fallback
    \$__siteHandle = '';
    if (isset(\$currentSite) && \$currentSite && (is_object(\$currentSite) || is_array(\$currentSite))) {
        try { \$__siteHandle = is_object(\$currentSite) ? (\$currentSite->handle ?? '') : (\$currentSite['handle'] ?? ''); } catch (\Throwable \$e) { \$__siteHandle = ''; }
    }
    if (!\$__siteHandle) {
        try { \$__siteHandle = \Craft::\$app->getSites()->getCurrentSite()->handle; } catch (\Throwable \$e) { \$__siteHandle = ''; }
    }

    echo \$__env->first(
        array_filter([
            \$__siteHandle ? \$__siteHandle . '.' . \$__template : null,
            \$__template
        ]),
        \$__data
    )->render();
?>
PHP;
    }

    /**
     * Compiler for the paginate directive.
     * Creates a paginator for an ElementQuery and makes page info and results available.
     */
    public static function compilePaginate(string $expression): string
    {
        return <<<PHP
<?php
\$__pgArgs = [{$expression}];

\$__pgQuery = \$__pgArgs[0] ?? null;
if (!\$__pgQuery) {
    throw new \\InvalidArgumentException("@paginate requires an ElementQuery as the first argument.");
}

\$__pgEntriesName = \$__pgArgs[1] ?? 'elements';
\$__pgInfoName    = \$__pgArgs[2] ?? 'pageInfo';
\$__pgConfig      = \$__pgArgs[3] ?? [];

if (!is_array(\$__pgConfig)) {
    \$__pgConfig = (array)\$__pgConfig;
}

// Default page size to the query limit (Twig-like). Fall back to 100 if no limit set.
\$__pgPageSize = \$__pgConfig['pageSize'] ?? (\$__pgQuery->limit ?? null);
if (!\$__pgPageSize) {
    \$__pgPageSize = 100;
}

\$__pgQuery->limit(null); // Remove limit for pagination count

\$__pgPaginator = new \\craft\\db\\Paginator(
    query: \$__pgQuery,
    config: array_merge(
        [
            'pageSize' => (int)\$__pgPageSize,
            'currentPage' => (int)(\$__pgConfig['currentPage'] ?? Craft::\$app->request->getPageNum()),
        ],
        \$__pgConfig
    )
);

\${\$__pgEntriesName} = collect(\$__pgPaginator->getPageResults());
\${\$__pgInfoName} = (new \\craft\\web\\twig\\variables\\Paginate())->create(\$__pgPaginator);
?>
PHP;
    }
}