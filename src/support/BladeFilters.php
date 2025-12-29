<?php

use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;
use craft\web\twig\Extension;

// Create a shared Craft Twig Extension instance that Blade filters can delegate to.
if (!isset($GLOBALS['__extension'])) {
    $GLOBALS['__extension'] = new Extension(\Craft::$app->getView(), \Craft::$app->getView()->twig);
}
/**
 * Blade filter helpers - wraps Craft CMS Twig filters for use in Blade templates.
 * Provides functionality equivalent to Twig filters without Twig dependency.
 *
 * Note: AI generated from Craft's Twig extension. Provided as is. May contain bugs.
 * Please review and test before use.
 */
// String case conversion filters
if (!function_exists('ascii')) {
    function ascii(mixed $string): string {
        return StringHelper::toAscii((string)$string);
    }
}
if (!function_exists('camel')) {
    function camel(mixed $string): string {
        global $__extension;
        return $__extension->camelFilter($string);
    }
}
if (!function_exists('kebab')) {
    function kebab(mixed $string, string $glue = '-', bool $lower = true, bool $removePunctuation = true): string {
        global $__extension;
        return $__extension->kebabFilter($string, $glue, $lower, $removePunctuation);
    }
}
if (!function_exists('pascal')) {
    function pascal(mixed $string): string {
        global $__extension;
        return $__extension->pascalFilter($string);
    }
}
if (!function_exists('snake')) {
    function snake(mixed $string): string {
        global $__extension;
        return $__extension->snakeFilter($string);
    }
}
if (!function_exists('lcfirst')) {
    function lcfirst(mixed $string): string {
        global $__extension;
        return $__extension->lcfirstFilter($string);
    }
}
if (!function_exists('ucfirst')) {
    function ucfirst(mixed $string): string {
        global $__extension;
        return $__extension->ucfirstFilter($string);
    }
}
if (!function_exists('ucwords')) {
    function ucwords(string $string): string {
        global $__extension;
        // Twig's ucwords filter needs environment; Extension handles that internally.
        return $__extension->ucwordsFilter(\Craft::$app->getView()->twig, $string);
    }
}
if (!function_exists('truncate')) {
    function truncate(string $string, int $length, string $suffix = '…', bool $splitSingleWord = true): string {
        global $__extension;
        return $__extension->truncateFilter($string, $length, $suffix, $splitSingleWord);
    }
}
if (!function_exists('widont')) {
    function widont(string $string): string {
        global $__extension;
        return $__extension->widontFilter($string);
    }
}
// Array/Collection filters
if (!function_exists('column')) {
    function column(array $array, string|int $columnKey, string|int|null $indexKey = null): array {
        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        }
        return ArrayHelper::getColumn($array, $columnKey, $indexKey);
    }
}
if (!function_exists('contains')) {
    function contains(mixed $needle, array $haystack, bool $strict = false): bool {
        if ($haystack instanceof \Traversable) {
            $haystack = iterator_to_array($haystack);
        }
        return ArrayHelper::contains($haystack, $needle, $strict);
    }
}
if (!function_exists('firstWhere')) {
    function firstWhere(array $array, string|callable $key, mixed $value = null): mixed {
        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        }
        return ArrayHelper::firstWhere($array, $key, $value);
    }
}
if (!function_exists('index')) {
    function index(array $array, string|callable $key): array {
        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        }
        return ArrayHelper::index($array, $key);
    }
}
if (!function_exists('where')) {
    function where(array $array, string|callable $key, mixed $value = null): array {
        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        }
        return ArrayHelper::where($array, $key, $value);
    }
}
if (!function_exists('flatten')) {
    function flatten(array $array, int $depth = PHP_INT_MAX): array {
        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        }
        $result = [];
        foreach ($array as $item) {
            if (is_array($item) || $item instanceof \Traversable) {
                if ($depth > 1) {
                    $flattened = flatten($item, $depth - 1);
                    array_push($result, ...$flattened);
                } else {
                    array_push($result, ...(array)$item);
                }
            } else {
                $result[] = $item;
            }
        }
        return $result;
    }
}
if (!function_exists('append')) {
    function append(mixed $value, mixed $append): mixed {
        global $__extension;
        return $__extension->appendFilter($value, $append);
    }
}
if (!function_exists('prepend')) {
    function prepend(mixed $value, mixed $prepend): mixed {
        global $__extension;
        return $__extension->prependFilter($value, $prepend);
    }
}
if (!function_exists('push')) {
    function push(array $array, mixed ...$elements): array {
        global $__extension;
        return $__extension->pushFilter($array, ...$elements);
    }
}
if (!function_exists('unshift')) {
    function unshift(array $array, mixed ...$elements): array {
        global $__extension;
        return $__extension->unshiftFilter($array, ...$elements);
    }
}
if (!function_exists('merge')) {
    function merge(array $array, array ...$arrays): array {
        global $__extension;
        $result = $array;
        foreach ($arrays as $arr2) {
            $result = $__extension->mergeFilter($result, $arr2, false);
        }
        return $result;
    }
}
if (!function_exists('without')) {
    function without(array $array, string|int ...$keys): array {
        global $__extension;
        return $__extension->withoutFilter($array, ...$keys);
    }
}
if (!function_exists('withoutKey')) {
    function withoutKey(array $array, mixed $value): array {
        global $__extension;
        return $__extension->withoutKeyFilter($array, $value);
    }
}
if (!function_exists('indexOf')) {
    function indexOf(array $array, mixed $needle, bool $strict = false): int|string|null {
        global $__extension;
        return $__extension->indexOfFilter($array, $needle, $strict ? 0 : null);
    }
}
if (!function_exists('replace')) {
    function replace(mixed $value, mixed $search, mixed $replace): mixed {
        global $__extension;
        return $__extension->replaceFilter($value, $search, $replace);
    }
}
if (!function_exists('group')) {
    function group(array $array, string|callable $key): array {
        global $__extension;
        return $__extension->groupFilter($array, $key);
    }
}
if (!function_exists('multisort')) {
    function multisort(array $array, string|array $keys, int $order = SORT_ASC): array {
        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        }
        if (is_string($keys)) {
            $keys = [$keys];
        }
        usort($array, function($a, $b) use ($keys, $order) {
            foreach ($keys as $key) {
                $aVal = is_array($a) ? ($a[$key] ?? null) : ($a->{$key} ?? null);
                $bVal = is_array($b) ? ($b[$key] ?? null) : ($b->{$key} ?? null);
                $cmp = $aVal <=> $bVal;
                if ($cmp !== 0) {
                    return $order === SORT_DESC ? -$cmp : $cmp;
                }
            }
            return 0;
        });
        return $array;
    }
}
// Number/Currency filters
if (!function_exists('currency')) {
    function currency(mixed $value, ?string $currency = null, array $options = [], array $textOptions = [], bool $stripZeros = false): string {
        global $__extension;
        return $__extension->currencyFilter($value, $currency, $options, $textOptions, $stripZeros);
    }
}
if (!function_exists('filesize')) {
    function filesize(mixed $value, ?int $decimals = null, array $options = [], array $textOptions = []): string {
        global $__extension;
        return $__extension->filesizeFilter($value, $decimals, $options, $textOptions);
    }
}
if (!function_exists('number')) {
    function number(mixed $value, ?int $decimals = null, array $options = [], array $textOptions = []): string {
        global $__extension;
        return $__extension->numberFilter($value, $decimals, $options, $textOptions);
    }
}
if (!function_exists('percentage')) {
    function percentage(mixed $value, ?int $decimals = null, array $options = [], array $textOptions = []): string {
        global $__extension;
        return $__extension->percentageFilter($value, $decimals, $options, $textOptions);
    }
}
if (!function_exists('money')) {
    function money(mixed $money, ?string $formatLocale = null): ?string {
        global $__extension;
        return $__extension->moneyFilter($money, $formatLocale);
    }
}

// Date/Time filters
if (!function_exists('timestamp')) {
    function timestamp(mixed $value): ?int {
        global $__extension;
        return $__extension->timestampFilter($value);
    }
}

// HTML/Markup filters
if (!function_exists('address')) {
    function address(?\craft\elements\Address $address, array $options = []): string {
        global $__extension;
        return $__extension->addressFilter($address, $options);
    }
}

if (!function_exists('markdown')) {
    function markdown(mixed $markdown, ?string $flavor = null, bool $inlineOnly = false, bool $encode = false): string {
        global $__extension;
        return $__extension->markdownFilter($markdown, $flavor, $inlineOnly, $encode);
    }
}

if (!function_exists('purify')) {
    function purify(string $html, ?array $config = null): string {
        global $__extension;
        return $__extension->purifyFilter($html, $config);
    }
}

if (!function_exists('removeClass')) {
    function removeClass(string $html, string|array $class): string {
        global $__extension;
        return $__extension->removeClassFilter($html, $class);
    }
}

if (!function_exists('parseAttr')) {
    function parseAttr(string $attrString): array {
        global $__extension;
        return $__extension->parseAttrFilter($attrString);
    }
}

if (!function_exists('parseRefs')) {
    function parseRefs(string $string): string {
        global $__extension;
        return $__extension->parseRefsFilter($string);
    }
}

// Encoding/Hashing filters
if (!function_exists('hash')) {
    function hash(mixed $value): string {
        return \Craft::$app->getSecurity()->hashData((string)$value);
    }
}

if (!function_exists('encenc')) {
    function encenc(mixed $value): string {
        global $__extension;
        return $__extension->encencFilter($value);
    }
}

// Complex filters that work with closures/callables
if (!function_exists('find')) {
    function find(mixed $value, mixed $arrow = null, mixed ...$args): mixed {
        global $__extension;
        return $__extension->findFilter(\Craft::$app->getView()->twig, $value, $arrow, ...$args);
    }
}

if (!function_exists('filter')) {
    function filter(mixed $value, mixed $arrow = null): mixed {
        global $__extension;
        return $__extension->filterFilter(\Craft::$app->getView()->twig, $value, $arrow);
    }
}

if (!function_exists('length')) {
    function length(mixed $value): int {
        global $__extension;
        return $__extension->lengthFilter(\Craft::$app->getView()->twig, $value);
    }
}

if (!function_exists('sort')) {
    function sort(mixed $array, mixed $arrow = null): mixed {
        global $__extension;
        return $__extension->sortFilter(\Craft::$app->getView()->twig, $array, $arrow);
    }
}

if (!function_exists('reduce')) {
    function reduce(mixed $array, mixed $arrow, mixed $initial = null): mixed {
        global $__extension;
        return $__extension->reduceFilter(\Craft::$app->getView()->twig, $array, $arrow, $initial);
    }
}

if (!function_exists('map')) {
    function map(mixed $array, mixed $arrow): mixed {
        global $__extension;
        return $__extension->mapFilter(\Craft::$app->getView()->twig, $array, $arrow);
    }
}

// Special/Output filters for date formatting
if (!function_exists('atom')) {
    function atom(mixed $date, mixed $timezone = null): string {
        global $__extension;
        return $__extension->atomFilter(\Craft::$app->getView()->twig, $date, $timezone);
    }
}

if (!function_exists('httpdate')) {
    function httpdate(mixed $date, mixed $timezone = null): string {
        global $__extension;
        return $__extension->httpdateFilter(\Craft::$app->getView()->twig, $date, $timezone);
    }
}

if (!function_exists('rss')) {
    function rss(mixed $date, mixed $timezone = null): string {
        global $__extension;
        return $__extension->rssFilter(\Craft::$app->getView()->twig, $date, $timezone);
    }
}

if (!function_exists('date')) {
    function date(mixed $date = null, mixed $timezone = null): \DateTimeInterface {
        global $__extension;
        return $__extension->dateFunction(\Craft::$app->getView()->twig, $date, $timezone);
    }
}

if (!function_exists('time')) {
    function time(mixed $date = null, mixed $timezone = null): string {
        global $__extension;
        return $__extension->timeFilter(\Craft::$app->getView()->twig, $date, $timezone);
    }
}

if (!function_exists('datetime')) {
    function datetime(mixed $date = null, mixed $timezone = null): string {
        global $__extension;
        return $__extension->datetimeFilter(\Craft::$app->getView()->twig, $date, $timezone);
    }
}
