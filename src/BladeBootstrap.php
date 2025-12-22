<?php

namespace wsydney76\blade;

use Craft;
use craft\helpers\App;
use craft\web\twig\Extension;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use wsydney76\blade\support\CraftContainer;

/**
 *  Blade view engine integration for Craft CMS.
 *
 *  Provides a simple interface to render Blade templates within Craft,
 *  including support for custom directives and global data sharing.
 * /
 * class Blade
 *
 * CAUTION: This is (mostly) AI generated code and may require adjustments to work properly.
 *
 * TODO: This is territory that requires deeper Laravel/Blade expertise.
 */
class BladeBootstrap
{
    protected Factory $viewFactory;
    protected BladeCompiler $bladeCompiler;

    public function __construct()
    {
        Craft::info('Initializing Blade view engine', __METHOD__);

        $this->boot(
            App::env('BLADE_VIEWS_PATH') ?: '/var/www/html/resources/views',
            App::env('BLADE_CACHE_PATH') ?: '/var/www/html/storage/runtime/blade/cache');

        foreach ($this->getGlobals() as $key => $value) {
            $this->share($key, $value);
        }

        // Render Twig directive
        $this->directive('renderTwig', function($expression) {
            return "<?php echo \\Craft::\$app->view->renderTemplate($expression); ?>";
        });

        // includeLocalized directive using extracted compiler method
        $this->directive('includeLocalized', function($expression) {
            return $this->compileIncludeLocalized([$expression]);
        });

        $this->directive('set', function ($expression) {
            return "<?php {$expression}; ?>";
        });

        $this->directive('paginate', function($expression) {
            return $this->compilePaginate($expression);
        });
    }

    protected function boot(string $viewsPath, string $cachePath): void
    {
        // Use custom container that provides getNamespace()
        $container = new CraftContainer();
        Container::setInstance($container);

        $filesystem = new Filesystem();
        $events = new Dispatcher($container);

        // Ensure the compiled cache directory exists
        if (!$filesystem->exists($cachePath)) {
            $filesystem->makeDirectory($cachePath, 0775, true, true);
        }

        // Bind core services into the container so Blade internals can resolve them
        // Provide a config repository with a get() method (required by components)
        $configItems = [
            'view' => [
                'paths' => [$viewsPath],
                'compiled' => $cachePath,
            ],
        ];

        $repoClass = 'Illuminate\\Config\\Repository';
        $container->instance('config', new $repoClass($configItems));

        $container->instance('files', $filesystem);

        // Also bind Container/Application contracts so app() works outside Laravel
        $container->instance(\Illuminate\Contracts\Container\Container::class, $container);
        $container->instance(\Illuminate\Contracts\Foundation\Application::class, $container);

        // Blade compiler
        $this->bladeCompiler = new BladeCompiler($filesystem, $cachePath);
        if (method_exists($this->bladeCompiler, 'setContainer')) {
            $this->bladeCompiler->setContainer($container);
        }
        $container->instance('blade.compiler', $this->bladeCompiler);

        // Enable component tags (required for x-dynamic-component)
        if (method_exists($this->bladeCompiler, 'component')) {
            // Register dynamic component support
            $this->bladeCompiler->component('dynamic-component', \Illuminate\View\DynamicComponent::class);
        }

        // Engine resolver
        $resolver = new EngineResolver();
        $resolver->register('blade', function() {
            return new CompilerEngine($this->bladeCompiler);
        });
        $resolver->register('php', function() use ($filesystem) {
            return new PhpEngine($filesystem);
        });

        // View finder
        $finder = new FileViewFinder($filesystem, [$viewsPath]);

        // Factory
        $this->viewFactory = new Factory($resolver, $finder, $events);
        if (method_exists($this->viewFactory, 'setContainer')) {
            $this->viewFactory->setContainer($container);
        }

        // Bind the Factory contracts so components can resolve Illuminate\Contracts\View\Factory
        $container->instance(Factory::class, $this->viewFactory);
        $container->instance(\Illuminate\Contracts\View\Factory::class, $this->viewFactory);
        $container->instance('view', $this->viewFactory);

        // If you use class-based components, register them here, e.g.:
        // if (method_exists($this->bladeCompiler, 'component')) {
        //     $this->bladeCompiler->component('image', \wsydney76\blade\View\Components\Image::class);
        // }
    }

    /**
     * Render a Blade view.
     * Accepts either a single view name or an array of view names.
     * When given an array, renders the first view that exists.
     */
    public function render(string|array $views, array $data = []): string
    {
        if (is_array($views)) {
            return $this->viewFactory->first($views, $data)->render();
        }
        return $this->viewFactory->make($views, $data)->render();
    }

    /**
     * Share global data with all views.
     */
    public function share(string $key, mixed $value): void
    {
        $this->viewFactory->share($key, $value);
    }

    /**
     * Register a custom Blade directive.
     */
    public function directive(string $name, callable $handler): void
    {
        $this->bladeCompiler->directive($name, $handler);
    }

    /**
     * Access the underlying View Factory if needed.
     */
    public function factory(): Factory
    {
        return $this->viewFactory;
    }

    /**
     * Access the Blade compiler if needed.
     */
    public function compiler(): BladeCompiler
    {
        return $this->bladeCompiler;
    }

    /**
     * Return global variables from Craft's Twig environment.
     *
     * @return array
     */
    protected function getGlobals(): array
    {
        $extension = new Extension(Craft::$app->getView(), Craft::$app->getView()->twig);
        return $extension->getGlobals();
    }

    /**
     * Compiler for the includeLocalized directive.
     * Builds a PHP snippet that attempts site-specific template first, then falls back.
     */
    public function compileIncludeLocalized(array $expression): string
    {
        // Wrap raw expression in array brackets to safely parse comma-separated args
        // $expression will look like: 'meta', ['entry' => $entry]
        return <<<'PHP'
<?php
    $__args = $expression;
    $__template = array_shift($__args);
    $__data = array_shift($__args) ?? [];
    $__site = $currentSite->handle ?? '';
    echo $__env->first(
        array_filter([
            $__site ? $__site . '.' . $__template : null,
            $__template
        ]),
        $__data
    )->render();
?>
PHP;
    }

    /**
     * Compiler for the paginate directive.
     * Creates a paginator for an ElementQuery and makes page info and results available.
     */
    public function compilePaginate(string $expression): string
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
