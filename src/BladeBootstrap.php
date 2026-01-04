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
 * Boots an Illuminate Blade runtime inside Craft CMS.
 *
 * This class wires up a minimal Illuminate container + view factory so Craft projects can:
 * - Render `.blade.php` templates from a configured views path
 * - Compile templates into a configured cache directory
 * - Register directives/conditionals/components just like in a Laravel app
 *
 * Key implementation notes:
 * - A lightweight container (`CraftContainer`) is used to satisfy Blade's expectations
 *   (most importantly `getNamespace()` for component class resolution).
 * - Laravel facades are enabled by setting the facade application instance.
 * - Only the services needed by Blade are bound (events, config, files, view factory, compiler).
 */
class BladeBootstrap
{


    public Factory $viewFactory;
    public BladeCompiler $bladeCompiler;

    /**
     * Create and boot the Blade runtime using plugin settings.
     *
     * @throws \yii\base\InvalidConfigException If environment parsing fails.
     */
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
     * @param string $viewsPath Absolute path to the Blade views directory
     * @param string $cachePath Absolute path to the compiled views cache directory
     */
    protected function boot(string $viewsPath, string $cachePath): void
    {
        // Use a custom container that provides Laravel's Application namespace.
        $container = new CraftContainer();
        Container::setInstance($container);

        // Ensure Laravel facades (including Illuminate\Support\Facades\Blade) use this container.
        Facade::setFacadeApplication($container);

        $filesystem = new Filesystem();
        $events = new Dispatcher($container);

        // Bind events so view composers can resolve the dispatcher.
        $container->instance('events', $events);
        $container->instance(\Illuminate\Contracts\Events\Dispatcher::class, $events);

        // Ensure the compiled cache directory exists.
        // Blade writes compiled PHP files here.
        if (!$filesystem->exists($cachePath)) {
            $filesystem->makeDirectory($cachePath, 0775, true, true);
        }

        // Bind a small config repository; some Blade features (components) expect `config('view.*')`.
        $configItems = [
            'view' => [
                'paths' => [$viewsPath],
                'compiled' => $cachePath,
            ],
        ];

        $repoClass = 'Illuminate\\Config\\Repository';
        $container->instance('config', new $repoClass($configItems));

        // Filesystem service used by Blade and the file view finder.
        $container->instance('files', $filesystem);

        // Bind Container/Application contracts so helpers like `app()` work outside Laravel.
        $container->instance(\Illuminate\Contracts\Container\Container::class, $container);
        $container->instance(\Illuminate\Contracts\Foundation\Application::class, $container);

        // Blade compiler.
        $this->bladeCompiler = new BladeCompiler($filesystem, $cachePath);
        if (method_exists($this->bladeCompiler, 'setContainer')) {
            $this->bladeCompiler->setContainer($container);
        }
        $container->instance('blade.compiler', $this->bladeCompiler);

        // Enable component tags (required for x-dynamic-component and class components).
        if (method_exists($this->bladeCompiler, 'component')) {
            $this->bladeCompiler->component('dynamic-component', \Illuminate\View\DynamicComponent::class);
        }

        // Engine resolver: maps template engines to renderers.
        $resolver = new EngineResolver();
        $resolver->register('blade', function() {
            return new CompilerEngine($this->bladeCompiler);
        });
        $resolver->register('php', function() use ($filesystem) {
            return new PhpEngine($filesystem);
        });

        // View finder: resolves dotted view names -> files under $viewsPath.
        $finder = new FileViewFinder($filesystem, [$viewsPath]);
        $container->instance('view.finder', $finder);

        // View factory.
        $this->viewFactory = new Factory($resolver, $finder, $events);
        if (method_exists($this->viewFactory, 'setContainer')) {
            $this->viewFactory->setContainer($container);
        }

        // Bind the Factory contracts so components can resolve Illuminate\Contracts\View\Factory.
        $container->instance(Factory::class, $this->viewFactory);
        $container->instance(\Illuminate\Contracts\View\Factory::class, $this->viewFactory);
        $container->instance('view', $this->viewFactory);

        // Register anonymous component paths from settings.
        // These map filesystem directories to `<x-...>` namespaces.
        foreach (BladePlugin::getInstance()->getSettings()->bladeComponentPaths as $componentPath) {
            $path = App::parseEnv($componentPath['path'] ?? '');
            $prefix = $componentPath['prefix'] ?? null;
            if ($path) {
                Blade::anonymousComponentPath($path, $prefix);
            }
        }
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
     *
     * @param string $name Name used as `@{name}` in templates.
     * @param callable $handler Receives the raw expression string and returns compiled PHP.
     */
    public function directive(string $name, callable $handler): void
    {
        $this->bladeCompiler->directive($name, $handler);
    }

    /**
     * Register a custom Blade conditional (Blade::if equivalent).
     */
    public function if(string $name, callable $handler): void
    {
        $this->bladeCompiler->if($name, $handler);
    }

    /**
     * Register a custom stringable handler for a class.
     *
     * @param class-string $class
     */
    public function stringable(string $class, callable $handler): void
    {
        $this->bladeCompiler->stringable($class, $handler);
    }

    /**
     * Register a class-based Blade component.
     */
    public function component(string $name, string $class, ?string $prefix = null): void
    {
        $this->bladeCompiler->component($name, $class, $prefix);
    }

    /**
     * Access the underlying View Factory.
     */
    public function factory(): Factory
    {
        return $this->viewFactory;
    }

    /**
     * Access the underlying Blade compiler.
     */
    public function compiler(): BladeCompiler
    {
        return $this->bladeCompiler;
    }

}
