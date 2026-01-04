<?php

namespace wsydney76\blade\controllers;

use Craft;
use craft\base\ElementInterface;
use craft\web\Controller;
use wsydney76\blade\Blade;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * Controller used as an element route target to render Blade views.
 *
 * This is primarily invoked via `Entry::EVENT_SET_ROUTE` when an entry template is configured as:
 * - `blade:some.view` OR
 * - `path/to/file.blade.php`
 *
 * It can also be invoked via a direct site route (registered by the plugin) like:
 * - `/blade/articles` => `articles`
 * - `/blade/articles/list/bydate` => `articles.list.bydate`
 *
 * Route params:
 * - `view` (string) The Blade view name. Can be dotted (`blog.entry`) or a slash path
 *   (`blog/entry`) which will be normalized to dotted form.
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
     * Render a Blade view.
     *
     * If this action is used as an element route target, the matched element will be injected
     * into the Blade view context using a variable name derived from the element's short class
     * name (lowercased):
     * - `craft\elements\Entry` => `$entry`
     * - `craft\commerce\elements\Product` => `$product`
     *
     * If invoked via the direct site route `/{prefix}/{view}`, no element is expected, and the
     * view is rendered without an element variable.
     *
     * @param string $view Blade view name (dotted or slash-delimited).
     * @return string Rendered Blade output.
     *
     * @throws BadRequestHttpException If the view name is invalid.
     * @throws NotFoundHttpException If the view cannot be rendered.
     */
    public function actionRenderTemplate(string $view, string $prefix = null): string
    {
        if ($prefix) {
            $view = $prefix . '/' . $view;
        }

        $view = $this->normalizeViewName($view);

        $context = [];

        // Get the matched element for element routes (if any).
        $element = Craft::$app->getUrlManager()->getMatchedElement();
        if ($element && ($element instanceof ElementInterface)) {
            $class = $this->getVariableName($element);
            $context[$class] = $element;
        }

        try {
            return Blade::renderTemplate($view, $context);
        } catch (\Throwable $e) {
            // Avoid leaking filesystem paths/details publicly.
            if (Craft::$app->getConfig()->getGeneral()->devMode) {
                throw $e;
            }

            throw new NotFoundHttpException('Blade view not found.');
        }
    }

    /**
     * Normalize and validate incoming view names.
     *
     * Accepts either:
     * - dotted names: `articles.list.bydate`
     * - slash paths:  `articles/list/bydate` (converted to dots)
     */
    private function normalizeViewName(string $view): string
    {

        $view = trim($view);
        $view = trim($view, "/ ");
        $view = str_replace('\\\u{0000}', '', $view);

        // Support slash paths from routes (convert to dotted view name).
        $view = str_replace(['\\\
', "\\\\"], '', $view);
        $view = str_replace('/', '.', $view);

        if ($view === '') {
            throw new BadRequestHttpException('Missing view name.');
        }

        // Disallow traversal-like segments.
        if (str_contains($view, '..')) {
            throw new BadRequestHttpException('Invalid view name.');
        }

        // Only allow safe characters in dotted notation.
        if (!preg_match('/^[A-Za-z0-9_.-]+$/', $view)) {
            throw new BadRequestHttpException('Invalid view name.');
        }

        // Disallow leading/trailing dots or empty segments (e.g. `foo..bar`).
        if (str_starts_with($view, '.') || str_ends_with($view, '.') || str_contains($view, '..')) {
            throw new BadRequestHttpException('Invalid view name.');
        }

        return $view;
    }

    /**
     * Returns the lowercase short class name from a fully qualified class name.
     *
     * Example:
     *  craft\commerce\elements\Product => product
     *
     * @param string|object $class
     * @return string
     */
    function getVariableName(string|object $class): string
    {
        $className = is_object($class) ? get_class($class) : $class;

        // Extract the short class name without namespace
        $basename = substr(strrchr($className, '\\'), 1);

        return strtolower($basename);
    }
}
