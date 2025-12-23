<?php

use craft\base\ElementInterface;
use craft\elements\Asset;
use craft\helpers\Html;
use craft\helpers\Template as TemplateHelper;
use craft\helpers\UrlHelper;
use craft\helpers\App;
use craft\helpers\Sequence;
use craft\models\EntryType;
use yii\base\InvalidArgumentException;
use yii\db\Expression;

// Provide wrappers as global helper functions for Blade templates.
// Skip ones that duplicate native PHP functions (ceil, floor, get_class, array_combine).

if (!function_exists('actionUrl')) {
    function actionUrl(string $path = '', array $params = [], ?string $scheme = null): string {
        return UrlHelper::actionUrl($path, $params, $scheme);
    }
}

if (!function_exists('alias')) {
    function alias(string $alias): string {
        return \Craft::getAlias($alias);
    }
}

// clone is a reserved keyword; provide clone_var instead
if (!function_exists('clone_var')) {
    function clone_var(mixed $var): mixed {
        return clone $var;
    }
}

if (!function_exists('collect')) {
    function collect(mixed $var): \Illuminate\Support\Collection {
        $collection = \Illuminate\Support\Collection::make($var);
        // If all items are elements, return ElementCollection
        if ($collection->isNotEmpty() && $collection->doesntContain(fn($item) => !$item instanceof ElementInterface)) {
            return \craft\elements\ElementCollection::make($collection);
        }
        return $collection;
    }
}

if (!function_exists('configure')) {
    function configure(object $object, array $properties): object {
        return \Craft::configure($object, $properties);
    }
}

if (!function_exists('cpUrl')) {
    function cpUrl(string $path = '', array $params = [], ?string $scheme = null): string {
        return UrlHelper::cpUrl($path, $params, $scheme);
    }
}

if (!function_exists('create')) {
    function create(array|string $config): object {
        return (object)\Craft::createObject($config);
    }
}

if (!function_exists('dataUrl')) {
    function dataUrl(Asset|string $file, ?string $mimeType = null): string {
        try {
            if ($file instanceof Asset) {
                return $file->getDataUrl();
            }
            return Html::dataUrl(\Craft::getAlias($file), $mimeType);
        } catch (InvalidArgumentException $e) {
            \Craft::warning($e->getMessage(), __METHOD__);
            return '';
        }
    }
}

if (!function_exists('dump')) {
    function dump(...$vars): string {
        if (!$vars) {
            return '';
        }
        $output = '';
        foreach ($vars as $var) {
            ob_start();
            \Craft::dump($var);
            $output .= str_replace('<code>', '<code style="display:block;">', ob_get_clean());
        }
        return $output;
    }
}

if (!function_exists('encodeUrl')) {
    function encodeUrl(string $url): string {
        return UrlHelper::encodeUrl($url);
    }
}

if (!function_exists('entryType')) {
    function entryType(string $handle): EntryType {
        $entryType = \Craft::$app->getEntries()->getEntryTypeByHandle($handle);
        if ($entryType === null) {
            throw new InvalidArgumentException("Invalid entry type handle: $handle");
        }
        return $entryType;
    }
}

if (!function_exists('expression')) {
    function expression(mixed $expression, array $params = [], array $config = []): Expression {
        return new Expression($expression, $params, $config);
    }
}

if (!function_exists('fieldValueSql')) {
    function fieldValueSql(\craft\base\FieldLayoutProviderInterface $provider, string $fieldHandle, ?string $key = null): ?string {
        return $provider->getFieldLayout()->getFieldByHandle($fieldHandle)->getValueSql($key);
    }
}

if (!function_exists('getenv_craft')) {
    function getenv_craft(string $name, mixed $default = null): mixed {
        return App::env($name) ?? $default;
    }
}

if (!function_exists('gql')) {
    function gql(string $query, ?array $variables = null, ?string $operationName = null): array {
        $schema = \craft\helpers\Gql::createFullAccessSchema();
        return \Craft::$app->getGql()->executeQuery($schema, $query, $variables, $operationName);
    }
}

if (!function_exists('parseEnv')) {
    function parseEnv(string $value): string {
        return App::parseEnv($value);
    }
}

if (!function_exists('parseBooleanEnv')) {
    function parseBooleanEnv(string $value): bool {
        return App::parseBooleanEnv($value);
    }
}

if (!function_exists('plugin')) {
    function plugin(string $handle): ?\craft\base\PluginInterface {
        return \Craft::$app->getPlugins()->getPlugin($handle);
    }
}

if (!function_exists('raw')) {
    function raw(string $string): string {
        return (string)TemplateHelper::raw($string);
    }
}

if (!function_exists('renderObjectTemplate')) {
    function renderObjectTemplate(string $template, mixed $object): string {
        return \Craft::$app->getView()->renderObjectTemplate($template, $object);
    }
}

if (!function_exists('seq')) {
    function seq(string $name, ?int $length = null, bool $next = true): int|string {
        if ($next) {
            return Sequence::next($name, $length);
        }
        return Sequence::current($name, $length);
    }
}

if (!function_exists('shuffle_arr')) {
    function shuffle_arr(iterable $arr): array {
        if ($arr instanceof \Traversable) {
            $arr = iterator_to_array($arr, false);
        } else {
            $arr = (array)$arr;
        }
        shuffle($arr);
        return $arr;
    }
}

if (!function_exists('siteUrl')) {
    function siteUrl(string $path = '', array $params = [], ?string $scheme = null, ?string $siteId = null): string {
        return UrlHelper::siteUrl($path, $params, $scheme, $siteId);
    }
}

if (!function_exists('url')) {
    function url(string $path = '', array $params = [], ?string $scheme = null): string {
        return UrlHelper::url($path, $params, $scheme);
    }
}

// Element authorization wrappers
if (!function_exists('canCreateDrafts')) {
    function canCreateDrafts(ElementInterface $element, ?\craft\elements\User $user = null): bool {
        return \Craft::$app->getElements()->canCreateDrafts($element, $user);
    }
}
if (!function_exists('canDelete')) {
    function canDelete(ElementInterface $element, ?\craft\elements\User $user = null): bool {
        return \Craft::$app->getElements()->canDelete($element, $user);
    }
}
if (!function_exists('canDeleteForSite')) {
    function canDeleteForSite(ElementInterface $element, ?\craft\elements\User $user = null): bool {
        return \Craft::$app->getElements()->canDeleteForSite($element, $user);
    }
}
if (!function_exists('canDuplicate')) {
    function canDuplicate(ElementInterface $element, ?\craft\elements\User $user = null): bool {
        return \Craft::$app->getElements()->canDuplicate($element, $user);
    }
}
if (!function_exists('canSave')) {
    function canSave(ElementInterface $element, ?\craft\elements\User $user = null): bool {
        return \Craft::$app->getElements()->canSave($element, $user);
    }
}
if (!function_exists('canView')) {
    function canView(ElementInterface $element, ?\craft\elements\User $user = null): bool {
        return \Craft::$app->getElements()->canView($element, $user);
    }
}

// HTML generation wrappers
if (!function_exists('actionInput')) {
    function actionInput(string $action, array $params = []): string {
        return Html::actionInput($action, $params);
    }
}
if (!function_exists('attr')) {
    function attr(array $attributes): string {
        return Html::renderTagAttributes($attributes);
    }
}
if (!function_exists('csrfInput')) {
    function csrfInput(): string {
        return Html::csrfInput();
    }
}
if (!function_exists('failMessageInput')) {
    function failMessageInput(string $message = null): string {
        return Html::failMessageInput($message);
    }
}
if (!function_exists('hiddenInput')) {
    function hiddenInput(string $name, string $value = null, array $options = []): string {
        return Html::hiddenInput($name, $value, $options);
    }
}
if (!function_exists('input')) {
    function input(string $type, string $name = null, string $value = null, array $options = []): string {
        return Html::input($type, $name, $value, $options);
    }
}
if (!function_exists('ol')) {
    function ol(array $items, array $attributes = []): string {
        return Html::ol($items, $attributes);
    }
}
if (!function_exists('redirectInput')) {
    function redirectInput(string $url): string {
        return Html::redirectInput($url);
    }
}
if (!function_exists('successMessageInput')) {
    function successMessageInput(string $message = null): string {
        return Html::successMessageInput($message);
    }
}
if (!function_exists('svg')) {
    function svg(Asset|string $svg, ?bool $sanitize = null, ?bool $namespace = null, ?string $class = null): string {
        $markup = Html::svg($svg, $sanitize, $namespace);
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
    function tag(string $type, array $attributes = []): string {
        $html = \craft\helpers\ArrayHelper::remove($attributes, 'html', '');
        $text = \craft\helpers\ArrayHelper::remove($attributes, 'text');
        if ($text !== null) {
            $html = Html::encode($text);
        }
        return Html::tag($type, $html, $attributes);
    }
}
if (!function_exists('ul')) {
    function ul(array $items, array $attributes = []): string {
        return Html::ul($items, $attributes);
    }
}

// DOM event wrappers (no-op if not used in Blade context)
if (!function_exists('head')) {
    function head(): void {
        \Craft::$app->getView()->head();
    }
}
if (!function_exists('beginBody')) {
    function beginBody(): void {
        \Craft::$app->getView()->beginBody();
    }
}
if (!function_exists('endBody')) {
    function endBody(): void {
        \Craft::$app->getView()->endBody();
    }
}
