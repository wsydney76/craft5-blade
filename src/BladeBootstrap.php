<?php

namespace wsydney76\blade;

use Craft;
use craft\helpers\App;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Facade;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use wsydney76\blade\support\CraftContainer;

/**
 * Blade view engine integration for Craft CMS.
 *
 * Provides a simple interface to render Blade templates within Craft,
 * including support for custom directives and global data sharing.
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

        $settings = BladePlugin::getInstance()->getSettings();

        $this->boot(
            App::parseEnv($settings->bladeViewsPath),
            App::parseEnv($settings->bladeCachePath)
        );
    }

    /**
     * Bootstrap the Blade view engine.
     *
     * @param string $viewsPath The path to the views directory
     * @param string $cachePath The path to the cache directory
     */
    protected function boot(string $viewsPath, string $cachePath): void
    {
        // Use custom container that provides getNamespace()
        $container = new CraftContainer();
        Container::setInstance($container);

        // Ensure Laravel facades (including Illuminate\Support\Facades\Blade) use this container
        Facade::setFacadeApplication($container);

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

        // Register anonymous component paths from settings
        foreach (BladePlugin::getInstance()->getSettings()->bladeComponentPaths as $componentPath) {
            $path = App::parseEnv($componentPath['path'] ?? '');
            $prefix = $componentPath['prefix'] ?? null;
            if ($path) {
                Blade::anonymousComponentPath($path, $prefix);
            }
        }

        Blade::anonymousComponentPath(
            App::parseEnv('@root/test'),
            'shared'
        );
    }

    /**
     * Render a Blade view.
     * Accepts either a single view name or an array of view names.
     * When given an array, renders the first view that exists.
     *
     * @param string|array $views The view name or array of view names
     * @param array $data The data to pass to the view
     * @return string The rendered view output
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
     *
     * @param string $key The variable name
     * @param mixed $value The value to share
     */
    public function share(string $key, mixed $value): void
    {
        $this->viewFactory->share($key, $value);
    }

    /**
     * Register a custom Blade directive.
     *
     * @param string $name The directive name
     * @param callable $handler The directive handler callback
     */
    public function directive(string $name, callable $handler): void
    {
        $this->bladeCompiler->directive($name, $handler);
    }

    /**
     * Register a custom Blade conditional (Blade::if equivalent).
     *
     * @param string $name The conditional name used in templates (e.g., @mycond)
     * @param callable $handler A callback that returns truthy/falsey
     */
    public function if(string $name, callable $handler): void
    {
        $this->bladeCompiler->if($name, $handler);
    }

    /**
     * Access the underlying View Factory if needed.
     *
     * @return Factory The View Factory instance
     */
    public function factory(): Factory
    {
        return $this->viewFactory;
    }

    /**
     * Access the Blade compiler if needed.
     *
     * @return BladeCompiler The Blade compiler instance
     */
    public function compiler(): BladeCompiler
    {
        return $this->bladeCompiler;
    }


}
