<?php

namespace wsydney76\blade\support;

use Craft;
use wsydney76\blade\View;
use wsydney76\blade\BladePlugin;

/**
 * Registers and compiles custom Blade directives.
 *
 * These directives are compiled into PHP at Blade compile-time.
 *
 * Built-in directives are registered first, followed by any custom directives configured
 * via `Settings::$bladeDirectives`.
 *
 * Notes:
 * - Directive handlers receive the raw expression string from the template.
 * - Most directives return a PHP snippet (string) that Blade injects into the compiled template.
 * - Some directives have side effects (e.g. redirect/header) and should be used with care.
 */
class BladeDirectives
{
    /**
     * Register all custom Blade directives.
     */
    public static function register(): void
    {
        // Render a Twig template and echo the result.
        // Example: @renderTwig('partials/_thing', ['foo' => 'bar'])
        View::directive('renderTwig', function (string $expression): string {
            return "<?php echo \\Craft::\$app->view->renderTemplate($expression); ?>";
        });

        // Render first existing view from: `{siteHandle}.{view}` then `{view}`.
        // Example: @includeLocalized('partials.meta', ['entry' => $entry])
        View::directive('includeLocalized', function (string $expression): string {
            return static::compileIncludeLocalized($expression);
        });

        // Guard directives (throw Yii HTTP exceptions)
        View::directive('requireAdmin', function (string $expression): string {
            return "<?php if (!\\Craft::\$app->getUser()->getIsAdmin()) { throw new \\yii\\web\\ForbiddenHttpException('Admin access required.'); } ?>";
        });

        View::directive('requireLogin', function (string $expression): string {
            return "<?php if (\\Craft::\$app->getUser()->getIsGuest()) { throw new \\yii\\web\\UnauthorizedHttpException('Login required.'); } ?>";
        });

        View::directive('requireGuest', function (string $expression): string {
            return "<?php if (!\\Craft::\$app->getUser()->getIsGuest()) { throw new \\yii\\web\\ForbiddenHttpException('Guest access required.'); } ?>";
        });

        View::directive('requirePermission', function (string $expression): string {
            return "<?php if (!\\Craft::\$app->getUser()->checkPermission($expression)) { throw new \\yii\\web\\ForbiddenHttpException('Insufficient permissions.'); } ?>";
        });

        // Redirect and end the request.
        // Example: @redirect('/login', 302)
        View::directive('redirect', function (string $expression): string {
            return static::compileRedirect($expression);
        });

        // Execute arbitrary PHP expression/assignment.
        // Example: @set($foo = 'bar')
        View::directive('set', function (string $expression): string {
            return "<?php {$expression}; ?>";
        });

        // Create a paginator and export results + pageInfo into the template scope.
        // Example: @paginate($query, 'elements', 'pageInfo', ['pageSize' => 10])
        View::directive('paginate', function (string $expression): string {
            return static::compilePaginate($expression);
        });

        // Convert Markdown -> HTML and then purify.
        View::directive('markdown', function (string $expression): string {
            return static::compileMarkdown($expression);
        });

        // Set a single response header.
        // Usage: @header("Cache-Control: max-age=3600")
        View::directive('header', function (string $expression): string {
            return static::compileHeader($expression);
        });

        // Craft template fragment cache (mirrors Twig's `{% cache %}...{% endcache %}` behavior).
        // Usage:
        //   @cache
        //      ...
        //   @endcache
        //
        // Note: For now we support the no-args form (same as `{% cache %}` with no options).
        View::directive('cache', function (string $expression): string {
            return static::compileCacheStart($expression);
        });

        View::directive('endcache', function (): string {
            return static::compileCacheEnd();
        });

        foreach (BladePlugin::getInstance()->getSettings()->bladeDirectives as $name => $handler) {
            View::directive($name, $handler);
        }
    }

    /**
     * Compiler for the includeLocalized directive.
     *
     * Expression formats supported:
     * - @includeLocalized('meta')
     * - @includeLocalized('meta', ['entry' => $entry])
     * - @includeLocalized(['meta', ['entry' => $entry]])
     *
     * The compiled template uses the Illuminate view environment (`$__env`) and calls:
     * `$__env->first([...])->render()`.
     */
    public static function compileIncludeLocalized(string $expression): string
    {
        // Wrap raw expression in array brackets to safely parse comma-separated args
        // $expression will look like: 'meta', ['entry' => $entry]
        $template = <<<'PHP'
<?php
    $__args = [%s];

    // Support both call styles: @includeLocalized('meta', [...]) and @includeLocalized(['meta', [...]])
    if (count($__args) === 1 && is_array($__args[0])) {
        $__args = $__args[0];
    }

    $__template = $__args[0] ?? null;
    $__data = $__args[1] ?? [];

    // Determine site handle from shared global (if present) or Craft as fallback
    $__siteHandle = '';
    if (isset($currentSite) && $currentSite && (is_object($currentSite) || is_array($currentSite))) {
        try { $__siteHandle = is_object($currentSite) ? ($currentSite->handle ?? '') : ($currentSite['handle'] ?? ''); } catch (\Throwable $e) { $__siteHandle = ''; }
    }
    if (!$__siteHandle) {
        try { $__siteHandle = \Craft::$app->getSites()->getCurrentSite()->handle; } catch (\Throwable $e) { $__siteHandle = ''; }
    }

    echo $__env->first(
        array_filter([
            $__siteHandle ? $__siteHandle . '.' . $__template : null,
            $__template
        ]),
        $__data
    )->render();
?>
PHP;

        return \sprintf($template, $expression);
    }

    /**
     * Compiler for the paginate directive.
     *
     * Side-effect: defines variables into template scope via variable-variables
     * (e.g. `$elements` and `$pageInfo`).
     */
    public static function compilePaginate(string $expression): string
    {
        $template = <<<'PHP'
<?php
$__pgArgs = [%s];

$__pgQuery = $__pgArgs[0] ?? null;
if (!$__pgQuery) {
    throw new \InvalidArgumentException("@paginate requires an ElementQuery as the first argument.");
}

$__pgEntriesName = $__pgArgs[1] ?? 'elements';
$__pgInfoName    = $__pgArgs[2] ?? 'pageInfo';
$__pgConfig      = $__pgArgs[3] ?? [];

if (!is_array($__pgConfig)) {
    $__pgConfig = (array)$__pgConfig;
}

$__pgResult = \wsydney76\blade\View::paginate(
    $__pgQuery,
    $__pgEntriesName,
    $__pgInfoName,
    $__pgConfig
);

${$__pgEntriesName} = $__pgResult[$__pgEntriesName] ?? null;
${$__pgInfoName} = $__pgResult[$__pgInfoName] ?? null;
?>
PHP;

        return \sprintf($template, $expression);
    }

    /**
     * Compiler for the markdown directive.
     *
     * Uses the global helper functions `markdown()` and `purify()` provided by BladeFilters.
     */
    public static function compileMarkdown(string $expression): string
    {
        $template = <<<'PHP'
<?php
$__mdArgs = [%s];

$__mdText = $__mdArgs[0] ?? '';
$__mdFlavor = $__mdArgs[1] ?? 'original';
$__mdConfig = $__mdArgs[2] ?? null;

echo purify(markdown($__mdText, $__mdFlavor), $__mdConfig);
?>
PHP;

        return \sprintf($template, $expression);
    }

    /**
     * Compiler for the redirect directive.
     *
     * Side-effects:
     * - Sets a redirect response
     * - Calls `Craft::$app->end()` (terminates the request)
     */
    private static function compileRedirect(string $expression): string
    {
        $template = <<<'PHP'
<?php
    $__redirectArgs = [%s];
  
    $__redirectUrl = $__redirectArgs[0] ?? null;
    if (!$__redirectUrl) {
        throw new \InvalidArgumentException("@redirect requires a URL as the first argument.");
    }

    $__redirectStatusCode = $__redirectArgs[1] ?? 302;
    
    if (!is_int($__redirectStatusCode)) {
        if (is_numeric($__redirectStatusCode)) {
            $__redirectStatusCode = (int)$__redirectStatusCode;
        } else {
            throw new \InvalidArgumentException("@redirect status code must be an integer.");
        }
    }

    \Craft::$app->getResponse()->redirect($__redirectUrl, $__redirectStatusCode);
    \Craft::$app->end();
?>
PHP;

        return \sprintf($template, $expression);
    }

    /**
     * Compiler for the header directive.
     *
     * Expects a single string argument containing `Header-Name: value`.
     */
    private static function compileHeader(string $expression): string
    {
        $template = <<<'PHP'
<?php
    $__headerParts = array_map('trim', explode(':', %s, 2));
    \Craft::$app->getResponse()->getHeaders()->set($__headerParts[0], $__headerParts[1] ?? '');
    unset($__headerParts);
?>
PHP;

        return \sprintf($template, $expression);
    }

    /**
     * Compiler for the cache directive (start).
     *
     * Generates PHP equivalent to Craft's compiled Twig `{% cache %}` tag.
     *
     * Current support:
     * - No-args usage only.
     *
     * Notes:
     * - Uses a static counter so nested blocks get unique variable names.
     * - Uses a deterministic cache key based on file + line (when available) + expression.
     */
    private static function compileCacheStart(string $expression): string
    {
        $template = <<<'PHP'
<?php
    $__bladeCacheService = \Craft::$app->getTemplateCaches();
    $__bladeCacheRequest = \Craft::$app->getRequest();

    // Use a global counter to avoid "Duplicate declaration of static variable" when multiple @cache blocks
    // exist in the same compiled template.
    $__bladeCacheCounterKey = '__wsydney76_blade_cache_counter';
    if (!isset($GLOBALS[$__bladeCacheCounterKey]) || !is_int($GLOBALS[$__bladeCacheCounterKey])) {
        $GLOBALS[$__bladeCacheCounterKey] = 0;
    }
    $GLOBALS[$__bladeCacheCounterKey]++;

    $__bladeCacheI = $GLOBALS[$__bladeCacheCounterKey];

    // Options (all optional): ['key' => string, 'global' => bool, 'duration' => ?string, 'expiration' => mixed]
    $__bladeCacheOptions = %s;

    if (!is_array($__bladeCacheOptions)) {
        $__bladeCacheOptions = (array)$__bladeCacheOptions;
    }

    $__bladeCacheGlobal = (bool)($__bladeCacheOptions['global'] ?? false);
    $__bladeCacheDuration = $__bladeCacheOptions['duration'] ?? null;
    $__bladeCacheExpiration = $__bladeCacheOptions['expiration'] ?? null;
    $__bladeCacheIf = $__bladeCacheOptions['if'] ?? true;
    $__bladeCacheUnless = $__bladeCacheOptions['unless'] ?? false;

    // Match Craft's ignore conditions (live preview or tokenized preview requests)
    ${"__bladeIgnoreCache{$__bladeCacheI}"} = ($__bladeCacheRequest->getIsLivePreview() || $__bladeCacheRequest->getToken()) || (!($__bladeCacheIf) || ($__bladeCacheUnless));

    if (!${"__bladeIgnoreCache{$__bladeCacheI}"}) {
        // Cache key: allow explicit override, otherwise use deterministic key
        $__bladeCacheKeyValue = $__bladeCacheOptions['key'] ?? null;
        if (!$__bladeCacheKeyValue) {
            $__bladeCacheKeyValue = hash('sha256', (__FILE__ ?? '') . '|' . (__LINE__ ?? '') . '|' . json_encode($__bladeCacheOptions));
        }
        ${"__bladeCacheKey{$__bladeCacheI}"} = (string)$__bladeCacheKeyValue;

        ${"__bladeCacheBody{$__bladeCacheI}"} = $__bladeCacheService->getTemplateCache(${"__bladeCacheKey{$__bladeCacheI}"}, $__bladeCacheGlobal, true);
    } else {
        ${"__bladeCacheBody{$__bladeCacheI}"} = null;
    }

    if (${"__bladeCacheBody{$__bladeCacheI}"} === null) {
        if (!${"__bladeIgnoreCache{$__bladeCacheI}"}) {
            // Keep original defaults (withResources=true, global=false) unless overridden
            $__bladeCacheService->startTemplateCache(true, $__bladeCacheGlobal);
        }
        ob_start();
?>
PHP;

        // Blade passes an empty string when directive has no parentheses.
        // Treat missing expression as an empty options array.
        $expr = trim($expression);
        if ($expr === '') {
            $expr = '[]';
        }

        return \sprintf($template, $expr);
    }

    /**
     * Compiler for the cache directive (end).
     */
    private static function compileCacheEnd(): string
    {
        $template = <<<'PHP'
<?php
        ${"__bladeCacheBody{$__bladeCacheI}"} = ob_get_clean();
        if (!${"__bladeIgnoreCache{$__bladeCacheI}"}) {
            $__bladeCacheService->endTemplateCache(
                ${"__bladeCacheKey{$__bladeCacheI}"},
                $__bladeCacheGlobal,
                $__bladeCacheDuration,
                $__bladeCacheExpiration,
                ${"__bladeCacheBody{$__bladeCacheI}"},
                true
            );
        }
    }

    echo ${"__bladeCacheBody{$__bladeCacheI}"};

    unset(
        $__bladeCacheService,
        $__bladeCacheRequest,
        $__bladeCacheI,
        $__bladeCacheOptions,
        $__bladeCacheGlobal,
        $__bladeCacheDuration,
        $__bladeCacheExpiration,
        $__bladeCacheKeyValue,
        $__bladeCacheIf,
        $__bladeCacheUnless
    );
?>
PHP;

        return $template;
    }
}
