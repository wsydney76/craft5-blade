<?php

namespace wsydney76\blade\controllers;

use craft\web\Controller;
use wsydney76\blade\Blade;

/**
 * Controller used as an element route target to render Blade views.
 *
 * This is primarily invoked via `Entry::EVENT_SET_ROUTE` when an entry template is configured as:
 * - `blade:some.view` OR
 * - `path/to/file.blade.php`
 *
 * Route params:
 * - `view` (string) The dotted Blade view name (e.g. `blog.entry`).
 *
 * Security note:
 * This endpoint is anonymous by default so it can be used for public site templates.
 * Lock it down if you plan to render views that should require authentication.
 */
class BaseBladeController extends Controller
{
    /**
     * Allow anonymous access.
     *
     * Craft accepts `true`, `false`, or an array of actions.
     * We keep it permissive so front-end routes can render Blade-backed templates.
     */
    protected array|bool|int $allowAnonymous = true;

    /**
     * Render a Blade view and return the rendered output.
     *
     * @param string $view Dotted view name. Example: `entries.blogIndex`.
     */
    public function actionRender(string $view): string
    {
        return Blade::render($view);
    }
}
