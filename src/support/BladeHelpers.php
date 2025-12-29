<?php

use craft\base\ElementInterface;
use craft\elements\Asset;
use craft\helpers\Html;
use craft\helpers\Sequence;
use craft\helpers\UrlHelper;
use craft\models\EntryType;
use craft\web\twig\Extension;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\db\Expression;

// Provide wrappers as global helper functions for Blade templates.
// Skip ones that duplicate native PHP functions (ceil, floor, get_class, array_combine).

if (!function_exists('app')) {
    /**
     * Get the available container instance.
     * This helper is required by Laravel Blade's stringable feature.
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
     * In this Craft CMS integration we don't have Laravel's global `view()` helper.
     * The Blade plugin boots an Illuminate container and binds the view factory as `view`.
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
    $GLOBALS['__extension'] = new Extension(\Craft::$app->getView(), \Craft::$app->getView()->twig);
}
if (!function_exists('actionUrl')) {
    function actionUrl(string $path = '', array $params = [], ?string $scheme = null): string
    {
        return UrlHelper::actionUrl($path, $params, $scheme);
    }
}

if (!function_exists('alias')) {
    function alias(string $alias): string
    {
        return \Craft::getAlias($alias);
    }
}

// clone is a reserved keyword; provide clone_var instead
if (!function_exists('clone_var')) {
    function clone_var(mixed $var): mixed
    {
        global $__extension;
        return $__extension->cloneFunction($var);
    }
}

if (!function_exists('collect')) {
    function collect(mixed $var): \Illuminate\Support\Collection
    {
        global $__extension;
        /** @var \Illuminate\Support\Collection $collection */
        $collection = $__extension->collectFunction($var);
        return $collection;
    }
}

if (!function_exists('configure')) {
    function configure(object $object, array $properties): object
    {
        return \Craft::configure($object, $properties);
    }
}

if (!function_exists('cpUrl')) {
    function cpUrl(string $path = '', array $params = [], ?string $scheme = null): string
    {
        return UrlHelper::cpUrl($path, $params, $scheme);
    }
}

if (!function_exists('create')) {
    function create(array|string $config): mixed
    {
        return \Craft::createObject($config);
    }
}

if (!function_exists('dataUrl')) {
    function dataUrl(Asset|string $file, ?string $mimeType = null): string
    {
        global $__extension;
        return $__extension->dataUrlFunction($file, $mimeType);
    }
}

if (!function_exists('dump')) {
    function dump(...$vars): string
    {
        global $__extension;
        // Craft's dumpFunction expects a Twig context; emulate with an empty context.
        return $__extension->dumpFunction([], ...$vars);
    }
}

if (!function_exists('encodeUrl')) {
    function encodeUrl(string $url): string
    {
        return UrlHelper::encodeUrl($url);
    }
}

if (!function_exists('entryType')) {
    function entryType(string $handle): EntryType
    {
        global $__extension;
        return $__extension->entryTypeFunction($handle);
    }
}

if (!function_exists('expression')) {
    function expression(mixed $expression, array $params = [], array $config = []): Expression
    {
        global $__extension;
        return $__extension->expressionFunction($expression, $params, $config);
    }
}

if (!function_exists('fieldValueSql')) {
    function fieldValueSql(\craft\base\FieldLayoutProviderInterface $provider, string $fieldHandle, ?string $key = null): ?string
    {
        global $__extension;
        return $__extension->fieldValueSqlFunction($provider, $fieldHandle, $key);
    }
}

if (!function_exists('gql')) {
    function gql(string $query, ?array $variables = null, ?string $operationName = null): array
    {
        global $__extension;
        return $__extension->gqlFunction($query, $variables, $operationName);
    }
}

if (!function_exists('plugin')) {
    function plugin(string $handle): ?\craft\base\PluginInterface
    {
        global $__extension;
        return $__extension->pluginFunction($handle);
    }
}

if (!function_exists('seq')) {
    function seq(string $name, ?int $length = null, bool $next = true): int|string
    {
        global $__extension;
        if ($next) {
            return $__extension->seqFunction($name, $length);
        }
        return Sequence::current($name, $length);
    }
}

if (!function_exists('shuffle_arr')) {
    function shuffle_arr(iterable $arr): array
    {
        global $__extension;
        return $__extension->shuffleFunction($arr);
    }
}

if (!function_exists('siteUrl')) {
    function siteUrl(string $path = '', array $params = [], ?string $scheme = null, ?string $siteId = null): string
    {
        return UrlHelper::siteUrl($path, $params, $scheme, $siteId);
    }
}

if (!function_exists('url')) {
    function url(string $path = '', array $params = [], ?string $scheme = null): string
    {
        return UrlHelper::url($path, $params, $scheme);
    }
}

// Element authorization wrappers
if (!function_exists('canCreateDrafts')) {
    function canCreateDrafts(ElementInterface $element, ?\craft\elements\User $user = null): bool
    {
        return \Craft::$app->getElements()->canCreateDrafts($element, $user);
    }
}
if (!function_exists('canDelete')) {
    function canDelete(ElementInterface $element, ?\craft\elements\User $user = null): bool
    {
        return \Craft::$app->getElements()->canDelete($element, $user);
    }
}
if (!function_exists('canDeleteForSite')) {
    function canDeleteForSite(ElementInterface $element, ?\craft\elements\User $user = null): bool
    {
        return \Craft::$app->getElements()->canDeleteForSite($element, $user);
    }
}
if (!function_exists('canDuplicate')) {
    function canDuplicate(ElementInterface $element, ?\craft\elements\User $user = null): bool
    {
        return \Craft::$app->getElements()->canDuplicate($element, $user);
    }
}
if (!function_exists('canSave')) {
    function canSave(ElementInterface $element, ?\craft\elements\User $user = null): bool
    {
        return \Craft::$app->getElements()->canSave($element, $user);
    }
}
if (!function_exists('canView')) {
    function canView(ElementInterface $element, ?\craft\elements\User $user = null): bool
    {
        return \Craft::$app->getElements()->canView($element, $user);
    }
}

// HTML generation wrappers
if (!function_exists('actionInput')) {
    function actionInput(string $action, array $params = []): string
    {
        return Html::actionInput($action, $params);
    }
}
if (!function_exists('attr')) {
    function attr(array $attributes): string
    {
        return Html::renderTagAttributes($attributes);
    }
}
if (!function_exists('csrfInput')) {
    function csrfInput(): string
    {
        return Html::csrfInput();
    }
}
if (!function_exists('failMessageInput')) {
    function failMessageInput(?string $message = null): string
    {
        return Html::failMessageInput($message);
    }
}
if (!function_exists('hiddenInput')) {
    function hiddenInput(string $name, ?string $value = null, array $options = []): string
    {
        return Html::hiddenInput($name, $value, $options);
    }
}
if (!function_exists('input')) {
    function input(string $type, ?string $name = null, ?string $value = null, array $options = []): string
    {
        return Html::input($type, $name, $value, $options);
    }
}
if (!function_exists('ol')) {
    function ol(array $items, array $attributes = []): string
    {
        return Html::ol($items, $attributes);
    }
}
if (!function_exists('redirectInput')) {
    function redirectInput(string $url): string
    {
        return Html::redirectInput($url);
    }
}
if (!function_exists('successMessageInput')) {
    function successMessageInput(?string $message = null): string
    {
        return Html::successMessageInput($message);
    }
}
if (!function_exists('svg')) {
    function svg(Asset|string $svg, ?bool $sanitize = null, ?bool $namespace = null, ?string $class = null): string
    {
        global $__extension;
        $markup = $__extension->svgFunction($svg, $sanitize, $namespace);

        // Keep Blade plugin's legacy `class` argument behavior.
        if ($class !== null) {
            \Craft::$app->getDeprecator()->log('svg()-class', 'The `class` argument of the `svg()` helper has been deprecated. Use attr filter/helpers instead.');
            try {
                $markup = Html::modifyTagAttributes($markup, ['class' => $class]);
            } catch (InvalidArgumentException $e) {
                \Craft::warning('Unable to add a class to the SVG: ' . $e->getMessage(), __METHOD__);
            }
        }

        return $markup;
    }
}
if (!function_exists('tag')) {
    function tag(string $type, array $attributes = []): string
    {
        global $__extension;



        return $__extension->tagFunction($type, $attributes);
    }
}

// DOM event wrappers (no-op if not used in Blade context)
if (!function_exists('head')) {
    function head(): void
    {
        \Craft::$app->getView()->head();
    }
}
if (!function_exists('beginBody')) {
    function beginBody(): void
    {
        \Craft::$app->getView()->beginBody();
    }
}
if (!function_exists('endBody')) {
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
            return Craft::t($category, (string)$message, $params, $language);
        } catch (InvalidConfigException) {
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
