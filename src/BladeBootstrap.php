<?php

namespace wsydney76\blade;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Compilers\BladeCompiler;

class BladeBootstrap
{
    protected Factory $viewFactory;
    protected BladeCompiler $bladeCompiler;

    public function __construct(
        string $viewsPath,
        string $cachePath
    ) {
        $this->boot($viewsPath, $cachePath);
    }

    protected function boot(string $viewsPath, string $cachePath): void
    {
        $container = new Container();
        $filesystem = new Filesystem();
        $events = new Dispatcher($container);

        // Blade compiler
        $this->bladeCompiler = new BladeCompiler($filesystem, $cachePath);

        // Engine resolver
        $resolver = new EngineResolver();
        $resolver->register('blade', function () {
            return new CompilerEngine($this->bladeCompiler);
        });
        $resolver->register('php', function () {
            return new PhpEngine();
        });

        // View finder
        $finder = new FileViewFinder($filesystem, [$viewsPath]);

        // Factory
        $this->viewFactory = new Factory($resolver, $finder, $events);
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
}
