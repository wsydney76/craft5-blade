<?php

namespace wsydney76\blade\models;

use Craft;
use craft\base\Model;

/**
 * Plugin settings for Blade.
 *
 * All path settings can be Yii aliases (e.g. `@root/...`) and/or environment variables
 * (they are resolved via `craft\helpers\App::parseEnv()` when Blade boots).
 */
class Settings extends Model
{
    /**
     * Base path where Blade view files live.
     *
     * A dotted view name like `blog.entry` maps to `{bladeViewsPath}/blog/entry.blade.php`.
     */
    public string $bladeViewsPath = '@root/resources/views';

    /**
     * Directory where compiled Blade templates are written.
     *
     * Must be writable by the PHP process.
     */
    public string $bladeCachePath = '@runtime/blade/cache';

    /**
     * Site route prefix for the view-rendering endpoint.
     *
     * With the default `blade`, URLs like `/blade/articles/list` will render the Blade view
     * `articles.list` (slashes are converted to dots).
     *
     * Must be a single URL segment (no slashes).
     */
    public string $bladeRoutePrefixes = 'blade';

    /**
     * Anonymous component paths.
     *
     * Each item should be an array like:
     *  - `path`   (string) filesystem directory
     *  - `prefix` (string|null) optional prefix namespace for `<x-prefix-*>`
     *
     * @var array<int, array{path?: string, prefix?: string|null}>
     */
    public array $bladeComponentPaths = [];

    /**
     * Additional shared variables for all Blade views.
     *
     * These are applied after Craft's Twig globals are shared (see `support/BladeShared.php`).
     *
     * Example:
     *  - `'siteName' => fn() => Craft::$app->getSites()->getCurrentSite()->name`
     *
     * @var array<string, mixed>
     */
    public array $bladeShared = [];

    /**
     * Additional custom Blade directives.
     *
     * The array key is the directive name (without `@`).
     * The value should be a callable with signature `function (string $expression): string`.
     *
     * Example:
     *  - `'datetime' => fn($expression) => "<?php echo ({$expression})->format('Y-m-d'); ?>"`
     *
     * @var array<string, callable>
     */
    public array $bladeDirectives = [];

    /**
     * Additional Blade stringable handlers.
     *
     * Keys are class strings; values are callables that receive an instance and return a string.
     *
     * @var array<class-string, callable>
     */
    public array $bladeStringables = [];

    /**
     * Additional Blade conditionals (Laravel-style `View::if`).
     *
     * The array key is the conditional name.
     * The value should be a callable returning a boolean.
     *
     * @var array<string, callable>
     */
    public array $bladeIfs = [];

    /**
     * Class-based Blade components to register at boot.
     *
     * This is a config-driven alternative to calling `View::component('alias', Class::class)`.
     *
     * Settings shape (map):
     *
     * ```php
     * 'bladeComponents' => [
     *     'alert' => \modules\main\components\Alert::class,
     * ```
     *
     * @var array<string, class-string>
     */
    public array $bladeComponents = [];

    /**
     * View composers to register at boot.
     *
     * This is a config-driven alternative to calling `View::composer()`.
     *
     * Settings shape (map):
     *
     * ```php
     * 'bladeViewComposers' => [
     *     'components.article.latest' => function ($view) {
     *         $view->with('latest', ...);
     *     }
     * ]
     * ```
     *
     * @var array<string, \Closure|string>
     */
    public array $bladeViewComposers = [];

    /**
     * IMPORTANT:
     * Only settings that are editable in the CP UI (`src/templates/_settings.twig`) should be
     * serialized/saved to Project Config.
     *
     * The remaining settings (shared vars/directives/ifs/stringables/components/view composers)
     * are config-file-only and may contain callables, which must never be written to Project Config.
     *
     * @return array<int, string>
     */
    public function fields(): array
    {
        return [
            'bladeViewsPath',
            'bladeCachePath',
            'bladeRoutePrefixes',
            'bladeComponentPaths',
        ];
    }

    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            ['bladeViewsPath', 'required'],
            ['bladeCachePath', 'required'],
            ['bladeRoutePrefixes', 'validateBladeRoutePrefix'],
        ]);
    }

    /**
     * Validate the `bladeRoutePrefixes` setting.
     *
     * Must be a single URL segment (no slashes) and limited to letters/numbers/dash/underscore.
     */
    public function validateBladeRoutePrefix(): void
    {
        $prefixes = explode(',', $this->bladeRoutePrefixes);

        foreach ($prefixes as $prefix) {

            $prefix = trim($prefix);

            if ($prefix === '') {
                $this->addError('bladeRoutePrefixes', 'Route prefix cannot be empty.');
                return;
            }

            if (!preg_match('/^[a-z0-9][a-z0-9_-]*$/i', $prefix)) {
                $this->addError('bladeRoutePrefixes', 'Route prefix must be a single URL segment (letters/numbers/dash/underscore).');
                return;
            }
        }
    }
}
