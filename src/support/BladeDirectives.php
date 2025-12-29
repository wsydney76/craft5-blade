<?php

namespace wsydney76\blade\support;

use Craft;
use wsydney76\blade\Blade;

/**
 * Registers and compiles custom Blade directives.
 *
 *  Note: AI generated from Craft's Twig extension. Provided as is. May contain bugs.
 *  Please review and test before use.
 */
class BladeDirectives
{
    /**
     * Register all custom Blade directives.
     */
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

        Blade::directive('requireAdmin', function($expression) {
            return "<?php if (!\\Craft::\$app->getUser()->getIsAdmin()) { throw new \\yii\\web\\ForbiddenHttpException('Admin access required.'); } ?>";
        });

        Blade::directive('requireLogin', function($expression) {
            return "<?php if (\\Craft::\$app->getUser()->getIsGuest()) { throw new \\yii\\web\\UnauthorizedHttpException('Login required.'); } ?>";
        });

        Blade::directive('requireGuest', function($expression) {
            return "<?php if (!\\Craft::\$app->getUser()->getIsGuest()) { throw new \\yii\\web\\ForbiddenHttpException('Guest access required.'); } ?>";
        });

        Blade::directive('requirePermission', function($expression) {
            return "<?php if (!\\Craft::\$app->getUser()->checkPermission($expression)) { throw new \\yii\\web\\ForbiddenHttpException('Insufficient permissions.'); } ?>";
        });

        Blade::directive('redirect', function($expression) {
            return static::compileRedirect($expression);
        });

        Blade::directive('set', function($expression) {
            return "<?php {$expression}; ?>";
        });

        Blade::directive('paginate', function($expression) {
            return static::compilePaginate($expression);
        });

        // markdown directive for combining markdown and purify
        Blade::directive('markdown', function($expression) {
            return static::compileMarkdown($expression);
        });
    }

    /**
     * Compiler for the includeLocalized directive.
     * Builds a PHP snippet that attempts site-specific template first, then falls back.
     *
     * @param string $expression The directive expression
     * @return string The compiled PHP code
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
     * Creates a paginator for an ElementQuery and makes page info and results available.
     *
     * @param string $expression The directive expression
     * @return string The compiled PHP code
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

$__pgResult = \wsydney76\blade\Blade::paginate(
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
     * Combines markdown processing with HTML purification.
     * Usage: @markdown($text, $purifierConfig)
     *
     * @param string $expression The directive expression
     * @return string The compiled PHP code
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
}

