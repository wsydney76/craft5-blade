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
     * Optional subdirectory within `bladeViewsPath`.
     *
     * (Currently unused by the bootstrapper, but kept for backwards compatibility/future use.)
     */
    public string $bladeViewsSubdir = '';

    /**
     * Site route prefix for the view-rendering endpoint.
     *
     * With the default `blade`, URLs like `/blade/articles/list` will render the Blade view
     * `articles.list` (slashes are converted to dots).
     *
     * Must be a single URL segment (no slashes).
     */
    public string $bladeRoutePrefix = 'blade';

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

    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            ['routePrefix', 'required'],
            ['routePrefix', 'string'],
            ['routePrefix', 'trim'],
            // Keep it to a single URL segment (letters/numbers/dash/underscore).
            ['routePrefix', 'match', 'pattern' => '/^[a-z0-9][a-z0-9_-]*$/i'],
            // ...
        ]);
    }
}
