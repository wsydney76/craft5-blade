<?php

namespace wsydney76\blade\controllers;

use Craft;
use craft\base\ElementInterface;
use craft\web\Controller;
use wsydney76\blade\Blade;
use yii\web\BadRequestHttpException;

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
     * Render a Blade view for the current element route request.
     *
     * This action is intended to be used as an element route target. It relies on Craft's
     * URL manager having matched an element for the current request (e.g. Entry, Category,
     * Commerce Product, etc).
     *
     * The matched element is injected into the Blade view context using a variable name
     * derived from the element's short class name (lowercased):
     * - `craft\elements\Entry` => `$entry`
     * - `craft\commerce\elements\Product` => `$product`
     *
     * @param string $view Dotted Blade view name (e.g. `blog.entry`) passed via route params.
     * @return string Rendered Blade output.
     *
     * @throws BadRequestHttpException If no element was matched for the current route.
     */
    public function actionRender(string $view): string
    {
        // Get the matched element for the current route.
        $element = Craft::$app->getUrlManager()->getMatchedElement();

        // Invalid requests (e.g. no 'live' element matched) shouldn't get here
        // as the route would not have been resolved and Craft throws a 404 earlier.
        // But just in case, we double-check and throw an error.
        if (!$element || !($element instanceof ElementInterface)) {
            throw new BadRequestHttpException('No element matched for the current route.');
        }

        // Use the element's short class name as the template variable (e.g. "entry", "product").
        $class = $this->getVariableName($element);

        return Blade::render($view, [
            $class => $element,
        ]);
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
