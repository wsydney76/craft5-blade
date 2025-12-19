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
        if (class_exists('Illuminate\\Config\\Repository')) {
            $repoClass = 'Illuminate\\Config\\Repository';
            $container->instance('config', new $repoClass($configItems));
        } else {
            // Minimal fallback with dot-notation get()
            $container->instance('config', new class($configItems) {
                private array $items;
                public function __construct(array $items) { $this->items = $items; }
                public function get(string $key, $default = null) {
                    $segments = $key !== '' ? explode('.', $key) : [];
                    $value = $this->items;
                    foreach ($segments as $seg) {
                        if (is_array($value) && array_key_exists($seg, $value)) {
                            $value = $value[$seg];
                        } else {
                            return $default;
                        }
                    }
                    return $value;
                }
            });
        }
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
     */
    public function render(string $view, array $data = []): string
    {
        return $this->viewFactory->make($view, $data)->render();
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
}
