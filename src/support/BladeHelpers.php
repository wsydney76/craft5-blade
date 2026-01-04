<?php

// Keep this file in the global namespace: it defines global helper functions.

// (No imports: PhpStorm can mis-resolve imported names in files that declare global functions.)

/**
 * Blade helper functions.
 *
 * This file defines *global* PHP helper functions so Blade templates can call helpers that mirror
 * Craft's Twig functions (and a couple of Laravel helpers).
 *
 * Implementation notes:
 * - Helpers are declared behind `function_exists()` so projects can override them.
 * - Many helpers delegate to Craft's Twig `Extension` methods.
 * - Because this file can be included from within a closure during plugin boot, we store the shared
 *   Twig Extension instance in `$GLOBALS['__extension']` so all `global $__extension;` usages can
 *   reference the same instance.
 */

// Provide wrappers as global helper functions for Blade templates.
// Skip ones that duplicate native PHP functions (ceil, floor, get_class, array_combine).

if (!function_exists('app')) {
    /**
     * Resolve a service from the Illuminate container used by this plugin, or return the container.
     *
     * @param string|null $abstract Service id/class name to resolve. When null, returns the container.
     * @param array<string, mixed> $parameters Parameters used when building the service.
     */
    function app(?string $abstract = null, array $parameters = []): mixed
    {
        $container = \Illuminate\Container\Container::getInstance();
        if (is_null($abstract)) {
            return $container;
        }

        return $container->make($abstract, $parameters);
    }
}

if (!function_exists('view')) {
    /**
     * Create an Illuminate view instance.
     *
     * @param string $view Dotted view name.
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $mergeData
     */
    function view(string $view, array $parameters = [], array $mergeData = []): mixed
    {
        /** @var \Illuminate\Contracts\View\Factory $factory */
        $factory = app('view');
        return $factory->make($view, $parameters, $mergeData);
    }
}

// Create a shared Craft Twig Extension instance that Blade helpers can delegate to.
// IMPORTANT: BladeHelpers.php is required via require_once from within BladePlugin::init(),
// and that can occur inside a callback/closure. To ensure the extension instance is truly global
// (so that `global $__extension;` inside helper functions resolves to the same variable),
// we must assign it into $GLOBALS.
if (!isset($GLOBALS['__extension'])) {
    $GLOBALS['__extension'] = new \craft\web\twig\Extension(\Craft::$app->getView(), \Craft::$app->getView()->twig);
}
if (!function_exists('actionUrl')) {
    /**
     * Generate an action URL.
     *
     * This is a thin wrapper around Craft's UrlHelper::actionUrl().
     *
     * @param string $path Controller/action path.
     * @param array<string, mixed> $params Query/body params.
     * @param string|null $scheme URL scheme override.
     */
    function actionUrl(string $path = '', array $params = [], ?string $scheme = null): string
    {
        return \craft\helpers\UrlHelper::actionUrl($path, $params, $scheme);
    }
}

if (!function_exists('alias')) {
    /**
     * Resolve a Yii alias.
     */
    function alias(string $alias): string
    {
        return \Craft::getAlias($alias);
    }
}

// clone is a reserved keyword; provide clone_var instead
if (!function_exists('clone_var')) {
    /**
     * Clone a variable using Craft's Twig extension implementation.
     *
     * Useful for duplicating arrays/objects in template context.
     */
    function clone_var(mixed $var): mixed
    {
        global $__extension;
        return $__extension->cloneFunction($var);
    }
}

if (!function_exists('collect')) {
    /**
     * Create an Illuminate collection.
     *
     * @return \Illuminate\Support\Collection
     */
    function collect(mixed $var): \Illuminate\Support\Collection
    {
        global $__extension;
        /** @var \Illuminate\Support\Collection $collection */
        $collection = $__extension->collectFunction($var);
        return $collection;
    }
}

if (!function_exists('configure')) {
    /**
     * Configure an object by setting properties.
     *
     * @param object $object
     * @param array<string, mixed> $properties
     */
    function configure(object $object, array $properties): object
    {
        return \Craft::configure($object, $properties);
    }
}

if (!function_exists('cpUrl')) {
    /**
     * Generate a control panel URL.
     *
     * @param string $path
     * @param array<string, mixed> $params
     * @param string|null $scheme
     */
    function cpUrl(string $path = '', array $params = [], ?string $scheme = null): string
    {
        return \craft\helpers\UrlHelper::cpUrl($path, $params, $scheme);
    }
}

if (!function_exists('create')) {
    /**
     * Create a Craft object from a config array/class string.
     *
     * @param array|string $config
     */
    function create(array|string $config): mixed
    {
        return \Craft::createObject($config);
    }
}

if (!function_exists('dataUrl')) {
    /**
     * Create a data URL (base64) for an asset or file.
     *
     * @param \craft\elements\Asset|string $file
     * @param string|null $mimeType
     */
    function dataUrl(\craft\elements\Asset|string $file, ?string $mimeType = null): string
    {
        global $__extension;
        return $__extension->dataUrlFunction($file, $mimeType);
    }
}

if (!function_exists('dump')) {
    /**
     * Dump variables for debugging.
     *
     * Returns the dump output as a string so it can be echoed from templates.
     */
    function dump(...$vars): string
    {
        global $__extension;
        // Craft's dumpFunction expects a Twig context; emulate with an empty context.
        return $__extension->dumpFunction([], ...$vars);
    }
}

if (!function_exists('encodeUrl')) {
    /**
     * Encode a URL.
     *
     * Wrapper around UrlHelper::encodeUrl().
     */
    function encodeUrl(string $url)
    {
        return \craft\helpers\UrlHelper::encodeUrl($url);
    }
}

if (!function_exists('entryType')) {
    /**
     * Resolve an entry type by handle.
     *
     * @return \craft\models\EntryType
     */
    function entryType(string $handle)
    {
        global $__extension;
        return $__extension->entryTypeFunction($handle);
    }
}

if (!function_exists('expression')) {
    /**
     * Create a DB Expression.
     *
     * @return \yii\db\Expression
     */
    function expression(mixed $expression, array $params = [], array $config = [])
    {
        global $__extension;
        return $__extension->expressionFunction($expression, $params, $config);
    }
}

if (!function_exists('fieldValueSql')) {
    /**
     * Return the SQL expression for a field value.
     */
    function fieldValueSql(\craft\base\FieldLayoutProviderInterface $provider, string $fieldHandle, ?string $key = null): ?string
    {
        global $__extension;
        return $__extension->fieldValueSqlFunction($provider, $fieldHandle, $key);
    }
}

if (!function_exists('gql')) {
    /**
     * Execute a GraphQL query against Craft.
     *
     * @param string $query
     * @param array|null $variables
     * @param string|null $operationName
     * @return array<mixed>
     */
    function gql(string $query, ?array $variables = null, ?string $operationName = null): array
    {
        global $__extension;
        return $__extension->gqlFunction($query, $variables, $operationName);
    }
}

if (!function_exists('plugin')) {
    /**
     * Get a plugin instance by handle.
     */
    function plugin(string $handle): ?\craft\base\PluginInterface
    {
        global $__extension;
        return $__extension->pluginFunction($handle);
    }
}

if (!function_exists('seq')) {
    /**
     * Generate (or read) a per-request sequence value.
     *
     * @param string $name
     * @param int|null $length
     * @param bool $next When true returns next value, otherwise returns current.
     * @return int|string
     */
    function seq(string $name, ?int $length = null, bool $next = true): int|string
    {
        global $__extension;
        if ($next) {
            return $__extension->seqFunction($name, $length);
        }
        return \craft\helpers\Sequence::current($name, $length);
    }
}

if (!function_exists('shuffle_arr')) {
    /**
     * Shuffle an iterable into a randomized array.
     */
    function shuffle_arr(iterable $arr): array
    {
        global $__extension;
        return $__extension->shuffleFunction($arr);
    }
}

if (!function_exists('siteUrl')) {
    /**
     * Generate a site URL.
     *
     * @param string $path
     * @param array<string, mixed> $params
     * @param string|null $scheme
     * @param string|null $siteId
     */
    function siteUrl(string $path = '', array $params = [], ?string $scheme = null, ?string $siteId = null): string
    {
        return \craft\helpers\UrlHelper::siteUrl($path, $params, $scheme, $siteId);
    }
}

if (!function_exists('url')) {
    /**
     * Generate a URL.
     *
     * @param string $path
     * @param array<string, mixed> $params
     * @param string|null $scheme
     */
    function url(string $path = '', array $params = [], ?string $scheme = null): string
    {
        return \craft\helpers\UrlHelper::url($path, $params, $scheme);
    }
}

if (!function_exists('canCreateDrafts')) {
    /**
     * Whether a user can create drafts for the given element.
     *
     * @param \craft\base\ElementInterface $element
     * @param \craft\elements\User|null $user
     */
    function canCreateDrafts($element, ?\craft\elements\User $user = null): bool
    {
        return \Craft::$app->getElements()->canCreateDrafts($element, $user);
    }
}

if (!function_exists('canDelete')) {
    /**
     * Whether a user can delete the given element.
     *
     * @param \craft\base\ElementInterface $element
     * @param \craft\elements\User|null $user
     */
    function canDelete($element, ?\craft\elements\User $user = null): bool
    {
        return \Craft::$app->getElements()->canDelete($element, $user);
    }
}

if (!function_exists('canDeleteForSite')) {
    /**
     * Whether a user can delete the given element for a particular site.
     *
     * @param \craft\base\ElementInterface $element
     * @param \craft\elements\User|null $user
     */
    function canDeleteForSite($element, ?\craft\elements\User $user = null): bool
    {
        return \Craft::$app->getElements()->canDeleteForSite($element, $user);
    }
}

if (!function_exists('canDuplicate')) {
    /**
     * Whether a user can duplicate the given element.
     *
     * @param \craft\base\ElementInterface $element
     * @param \craft\elements\User|null $user
     */
    function canDuplicate($element, ?\craft\elements\User $user = null): bool
    {
        return \Craft::$app->getElements()->canDuplicate($element, $user);
    }
}

if (!function_exists('canSave')) {
    /**
     * Whether a user can save the given element.
     */
    function canSave(\craft\base\ElementInterface $element, ?\craft\elements\User $user = null): bool
    {
        return \Craft::$app->getElements()->canSave($element, $user);
    }
}

if (!function_exists('canView')) {
    /**
     * Whether a user can view the given element.
     */
    function canView(\craft\base\ElementInterface $element, ?\craft\elements\User $user = null): bool
    {
        return \Craft::$app->getElements()->canView($element, $user);
    }
}

if (!function_exists('actionInput')) {
    /**
     * Render Craft's `action` hidden input.
     */
    function actionInput(string $action, array $params = []): string
    {
        return \craft\helpers\Html::actionInput($action, $params);
    }
}

if (!function_exists('attr')) {
    /**
     * Render HTML tag attributes from an array.
     *
     * @param array<string, mixed> $attributes
     */
    function attr(array $attributes): string
    {
        return \craft\helpers\Html::renderTagAttributes($attributes);
    }
}

if (!function_exists('csrfInput')) {
    /**
     * Render a CSRF hidden input.
     */
    function csrfInput(): string
    {
        return \craft\helpers\Html::csrfInput();
    }
}

if (!function_exists('failMessageInput')) {
    /**
     * Render a fail message input.
     */
    function failMessageInput(?string $message = null): string
    {
        return \craft\helpers\Html::failMessageInput($message);
    }
}

if (!function_exists('hiddenInput')) {
    /**
     * Render a hidden input.
     */
    function hiddenInput(string $name, ?string $value = null, array $options = []): string
    {
        return \craft\helpers\Html::hiddenInput($name, $value, $options);
    }
}

if (!function_exists('input')) {
    /**
     * Render an input.
     */
    function input(string $type, ?string $name = null, ?string $value = null, array $options = []): string
    {
        return \craft\helpers\Html::input($type, $name, $value, $options);
    }
}

if (!function_exists('ol')) {
    /**
     * Render an ordered list.
     */
    function ol(array $items, array $attributes = []): string
    {
        return \craft\helpers\Html::ol($items, $attributes);
    }
}

if (!function_exists('redirectInput')) {
    /**
     * Render a redirect input.
     */
    function redirectInput(string $url): string
    {
        return \craft\helpers\Html::redirectInput($url);
    }
}

if (!function_exists('successMessageInput')) {
    /**
     * Render a success message input.
     */
    function successMessageInput(?string $message = null): string
    {
        return \craft\helpers\Html::successMessageInput($message);
    }
}

if (!function_exists('svg')) {
    /**
     * Render an SVG asset or string.
     *
     * Note: `$class` is deprecated but kept for backwards compatibility.
     */
    function svg(\craft\elements\Asset|string $svg, ?bool $sanitize = null, ?bool $namespace = null, ?string $class = null): string
    {
        global $__extension;
        $markup = $__extension->svgFunction($svg, $sanitize, $namespace);

        // Keep Blade plugin's legacy `class` argument behavior.
        if ($class !== null) {
            \Craft::$app->getDeprecator()->log('svg()-class', 'The `class` argument of the `svg()` helper has been deprecated. Use attr filter/helpers instead.');
            try {
                $markup = \craft\helpers\Html::modifyTagAttributes($markup, ['class' => $class]);
            } catch (\yii\base\InvalidArgumentException $e) {
                \Craft::warning('Unable to add a class to the SVG: ' . $e->getMessage(), __METHOD__);
            }
        }

        return $markup;
    }
}
if (!function_exists('tag')) {
    /**
     * Render an HTML tag.
     *
     * Delegates to Craft's Twig extension `tag` function.
     *
     * @param string $type Tag name.
     * @param array<string, mixed> $attributes
     */
    function tag(string $type, array $attributes = []): string
    {
        global $__extension;
        return $__extension->tagFunction($type, $attributes);
    }
}

// DOM event wrappers (no-op if not used in Blade context)
if (!function_exists('head')) {
    /**
     * Output/queue the document <head> contents.
     */
    function head(): void
    {
        \Craft::$app->getView()->head();
    }
}
if (!function_exists('beginBody')) {
    /**
     * Begin the document <body> section.
     */
    function beginBody(): void
    {
        \Craft::$app->getView()->beginBody();
    }
}
if (!function_exists('endBody')) {
    /**
     * End the document <body> section.
     */
    function endBody(): void
    {
        \Craft::$app->getView()->endBody();
    }
}

/**
 * Translates the given message.
 *
 * @param mixed $message The message to be translated.
 * @param string|array|null $category the message category.
 * @param array|string|null $params The parameters that will be used to replace the corresponding placeholders in the message.
 * @param string|null $language The language code (e.g. `en-US`, `en`). If this is null, the current
 * [[\yii\base\Application::language|application language]] will be used.
 * @return string the translated message.
 */
if (!function_exists('__')) {
    function __(mixed $message, mixed $category = null, mixed $params = null, ?string $language = null): string
    {
        // The front end site doesn't need to specify the category
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        if (is_array($category)) {
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $language = $params;
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $params = $category;
            $category = 'site';
        } elseif ($category === null) {
            $category = 'site';
        }

        if ($params === null) {
            $params = [];
        }

        try {
            return \Craft::t($category, (string)$message, $params, $language);
        } catch (\yii\base\InvalidConfigException) {
            return $message;
        }
    }
}

// Alias for the translation function
if (!function_exists('t')) {
    function t(mixed $message, mixed $category = null, mixed $params = null, ?string $language = null): string
    {
        return __(message: $message, category: $category, params: $params, language: $language);
    }
}
