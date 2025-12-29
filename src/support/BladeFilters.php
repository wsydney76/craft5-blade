<?php

/**
 * Blade filter helpers.
 *
 * This file defines *global* PHP functions that mirror Craft's Twig filters so they can be used
 * directly from Blade templates.
 *
 * Notes:
 * - Many functions delegate to Craft's Twig `Extension` filter implementations.
 * - Some names intentionally collide with common PHP/Twig names (`date`, `time`, `ucfirst`, `filesize`).
 *   We guard with `function_exists()`, but a project can still override these by defining its own
 *   helpers earlier in the bootstrap.
 */

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
    /**
     * Convert a string to ASCII.
     */
    function ascii(mixed $string): string {
        return StringHelper::toAscii((string)$string);
    }
}

if (!function_exists('camel')) {
    /**
     * Convert a string to camelCase.
     */
    function camel(mixed $string): string {
        global $__extension;
        return $__extension->camelFilter($string);
    }
}

if (!function_exists('kebab')) {
    /**
     * Convert a string to kebab-case.
     */
    function kebab(mixed $string, string $glue = '-', bool $lower = true, bool $removePunctuation = true): string {
        global $__extension;
        return $__extension->kebabFilter($string, $glue, $lower, $removePunctuation);
    }
}

if (!function_exists('pascal')) {
    /**
     * Convert a string to PascalCase.
     */
    function pascal(mixed $string): string {
        global $__extension;
        return $__extension->pascalFilter($string);
    }
}

if (!function_exists('snake')) {
    /**
     * Convert a string to snake_case.
     */
    function snake(mixed $string): string {
        global $__extension;
        return $__extension->snakeFilter($string);
    }
}

if (!function_exists('lcfirst')) {
    /**
     * Lowercase the first character.
     */
    function lcfirst(mixed $string): string {
        global $__extension;
        return $__extension->lcfirstFilter($string);
    }
}

if (!function_exists('ucfirst')) {
    /**
     * Uppercase the first character.
     */
    function ucfirst(mixed $string): string {
        global $__extension;
        return $__extension->ucfirstFilter($string);
    }
}

if (!function_exists('ucwords')) {
    /**
     * Uppercase the first character of each word.
     */
    function ucwords(string $string): string {
        global $__extension;
        // Twig's ucwords filter needs environment; Extension handles that internally.
        return $__extension->ucwordsFilter(\Craft::$app->getView()->twig, $string);
    }
}

if (!function_exists('truncate')) {
    /**
     * Truncate a string.
     */
    function truncate(string $string, int $length, string $suffix = '…', bool $splitSingleWord = true): string {
        global $__extension;
        return $__extension->truncateFilter($string, $length, $suffix, $splitSingleWord);
    }
}

if (!function_exists('widont')) {
    /**
     * Prevent widows in text (keeps last two words together where possible).
     */
    function widont(string $string): string {
        global $__extension;
        return $__extension->widontFilter($string);
    }
}

// Array/Collection filters
if (!function_exists('column')) {
    /**
     * Extract a column from an array.
     */
    function column(array $array, string|int $columnKey, string|int|null $indexKey = null): array {
        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        }
        return ArrayHelper::getColumn($array, $columnKey, $indexKey);
    }
}

if (!function_exists('contains')) {
    /**
     * Check whether an array contains a value.
     */
    function contains(mixed $needle, array $haystack, bool $strict = false): bool {
        if ($haystack instanceof \Traversable) {
            $haystack = iterator_to_array($haystack);
        }
        return ArrayHelper::contains($haystack, $needle, $strict);
    }
}

if (!function_exists('firstWhere')) {
    /**
     * Return the first element matching a key/value or callable predicate.
     */
    function firstWhere(array $array, string|callable $key, mixed $value = null): mixed {
        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        }
        return ArrayHelper::firstWhere($array, $key, $value);
    }
}

if (!function_exists('index')) {
    /**
     * Index an array by a key or callable.
     */
    function index(array $array, string|callable $key): array {
        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        }
        return ArrayHelper::index($array, $key);
    }
}

if (!function_exists('where')) {
    /**
     * Filter an array by a key/value or callable predicate.
     */
    function where(array $array, string|callable $key, mixed $value = null): array {
        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        }
        return ArrayHelper::where($array, $key, $value);
    }
}

if (!function_exists('flatten')) {
    /**
     * Flatten a nested array/traversable into a single array.
     *
     * @param array $array
     * @param int $depth Maximum nesting depth to flatten.
     * @return array
     */
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
    /**
     * Append a value to an array/string.
     */
    function append(mixed $value, mixed $append): mixed {
        global $__extension;
        return $__extension->appendFilter($value, $append);
    }
}

if (!function_exists('prepend')) {
    /**
     * Prepend a value to an array/string.
     */
    function prepend(mixed $value, mixed $prepend): mixed {
        global $__extension;
        return $__extension->prependFilter($value, $prepend);
    }
}

if (!function_exists('push')) {
    /**
     * Push one or more elements onto the end of an array.
     */
    function push(array $array, mixed ...$elements): array {
        global $__extension;
        return $__extension->pushFilter($array, ...$elements);
    }
}

if (!function_exists('unshift')) {
    /**
     * Add one or more elements to the beginning of an array.
     */
    function unshift(array $array, mixed ...$elements): array {
        global $__extension;
        return $__extension->unshiftFilter($array, ...$elements);
    }
}

if (!function_exists('merge')) {
    /**
     * Merge arrays.
     */
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
    /**
     * Return the array without the given keys.
     */
    function without(array $array, string|int ...$keys): array {
        global $__extension;
        return $__extension->withoutFilter($array, ...$keys);
    }
}

if (!function_exists('withoutKey')) {
    /**
     * Return the array without any elements matching the given value.
     */
    function withoutKey(array $array, mixed $value): array {
        global $__extension;
        return $__extension->withoutKeyFilter($array, $value);
    }
}

if (!function_exists('indexOf')) {
    /**
     * Return the index/key of the first occurrence of a value.
     */
    function indexOf(array $array, mixed $needle, bool $strict = false): int|string|null {
        global $__extension;
        return $__extension->indexOfFilter($array, $needle, $strict ? 0 : null);
    }
}

if (!function_exists('replace')) {
    /**
     * Replace values/strings.
     */
    function replace(mixed $value, mixed $search, mixed $replace): mixed {
        global $__extension;
        return $__extension->replaceFilter($value, $search, $replace);
    }
}

if (!function_exists('group')) {
    /**
     * Group an array by a key or callable.
     */
    function group(array $array, string|callable $key): array {
        global $__extension;
        return $__extension->groupFilter($array, $key);
    }
}

// Number/Currency filters
if (!function_exists('currency')) {
    /**
     * Format a number as a currency string.
     */
    function currency(mixed $value, ?string $currency = null, array $options = [], array $textOptions = [], bool $stripZeros = false): string {
        global $__extension;
        return $__extension->currencyFilter($value, $currency, $options, $textOptions, $stripZeros);
    }
}

if (!function_exists('filesize')) {
    /**
     * Format bytes as a human-readable file size.
     */
    function filesize(mixed $value, ?int $decimals = null, array $options = [], array $textOptions = []): string {
        global $__extension;
        return $__extension->filesizeFilter($value, $decimals, $options, $textOptions);
    }
}

if (!function_exists('number')) {
    /**
     * Format a number.
     */
    function number(mixed $value, ?int $decimals = null, array $options = [], array $textOptions = []): string {
        global $__extension;
        return $__extension->numberFilter($value, $decimals, $options, $textOptions);
    }
}

if (!function_exists('percentage')) {
    /**
     * Format a number as a percentage.
     */
    function percentage(mixed $value, ?int $decimals = null, array $options = [], array $textOptions = []): string {
        global $__extension;
        return $__extension->percentageFilter($value, $decimals, $options, $textOptions);
    }
}

if (!function_exists('money')) {
    /**
     * Format a Money object/value.
     */
    function money(mixed $money, ?string $formatLocale = null): ?string {
        global $__extension;
        return $__extension->moneyFilter($money, $formatLocale);
    }
}

// Date/Time filters
if (!function_exists('timestamp')) {
    /**
     * Convert a date/time input into a unix timestamp.
     */
    function timestamp(mixed $value): ?int {
        global $__extension;
        return $__extension->timestampFilter($value);
    }
}

// HTML/Markup filters
if (!function_exists('address')) {
    /**
     * Render an address element into HTML.
     */
    function address(?\craft\elements\Address $address, array $options = []): string {
        global $__extension;
        return $__extension->addressFilter($address, $options);
    }
}

if (!function_exists('markdown')) {
    /**
     * Convert Markdown to HTML.
     */
    function markdown(mixed $markdown, ?string $flavor = null, bool $inlineOnly = false, bool $encode = false): string {
        global $__extension;
        return $__extension->markdownFilter($markdown, $flavor, $inlineOnly, $encode);
    }
}

if (!function_exists('purify')) {
    /**
     * Purify an HTML string.
     */
    function purify(string $html, ?array $config = null): string {
        global $__extension;
        return $__extension->purifyFilter($html, $config);
    }
}

if (!function_exists('removeClass')) {
    /**
     * Remove one or more CSS classes from an HTML tag string.
     */
    function removeClass(string $html, string|array $class): string {
        global $__extension;
        return $__extension->removeClassFilter($html, $class);
    }
}

if (!function_exists('parseAttr')) {
    /**
     * Parse an HTML attribute string into an array.
     */
    function parseAttr(string $attrString): array {
        global $__extension;
        return $__extension->parseAttrFilter($attrString);
    }
}

if (!function_exists('parseRefs')) {
    /**
     * Parse Craft reference tags in a string.
     */
    function parseRefs(string $string): string {
        global $__extension;
        return $__extension->parseRefsFilter($string);
    }
}

// Encoding/Hashing filters
if (!function_exists('hash')) {
    /**
     * Hash a value using Craft's security service.
     */
    function hash(mixed $value): string {
        return \Craft::$app->getSecurity()->hashData((string)$value);
    }
}

if (!function_exists('encenc')) {
    /**
     * Double-encode a value.
     */
    function encenc(mixed $value): string {
        global $__extension;
        return $__extension->encencFilter($value);
    }
}

// Complex filters that work with closures/callables
if (!function_exists('find')) {
    /**
     * Find an element in an array/collection using an arrow function.
     */
    function find(mixed $value, mixed $arrow = null, mixed ...$args): mixed {
        global $__extension;
        return $__extension->findFilter(\Craft::$app->getView()->twig, $value, $arrow, ...$args);
    }
}

if (!function_exists('filter')) {
    /**
     * Filter items using an arrow function.
     */
    function filter(mixed $value, mixed $arrow = null): mixed {
        global $__extension;
        return $__extension->filterFilter(\Craft::$app->getView()->twig, $value, $arrow);
    }
}

if (!function_exists('length')) {
    /**
     * Get the length of a value (string/array/Countable/etc.).
     */
    function length(mixed $value): int {
        global $__extension;
        return $__extension->lengthFilter(\Craft::$app->getView()->twig, $value);
    }
}

if (!function_exists('sort')) {
    /**
     * Sort an array/collection using an optional arrow function.
     */
    function sort(mixed $array, mixed $arrow = null): mixed {
        global $__extension;
        return $__extension->sortFilter(\Craft::$app->getView()->twig, $array, $arrow);
    }
}

if (!function_exists('reduce')) {
    /**
     * Reduce an array/collection to a single value.
     */
    function reduce(mixed $array, mixed $arrow, mixed $initial = null): mixed {
        global $__extension;
        return $__extension->reduceFilter(\Craft::$app->getView()->twig, $array, $arrow, $initial);
    }
}

if (!function_exists('map')) {
    /**
     * Map items using an arrow function.
     */
    function map(mixed $array, mixed $arrow): mixed {
        global $__extension;
        return $__extension->mapFilter(\Craft::$app->getView()->twig, $array, $arrow);
    }
}

// Special/Output filters for date formatting
if (!function_exists('atom')) {
    /**
     * Format a date as ATOM (RFC 3339) string.
     */
    function atom(mixed $date, mixed $timezone = null): string {
        global $__extension;
        return $__extension->atomFilter(\Craft::$app->getView()->twig, $date, $timezone);
    }
}

if (!function_exists('httpdate')) {
    /**
     * Format a date as HTTP-date string.
     */
    function httpdate(mixed $date, mixed $timezone = null): string {
        global $__extension;
        return $__extension->httpdateFilter(\Craft::$app->getView()->twig, $date, $timezone);
    }
}

if (!function_exists('rss')) {
    /**
     * Format a date as RSS date string.
     */
    function rss(mixed $date, mixed $timezone = null): string {
        global $__extension;
        return $__extension->rssFilter(\Craft::$app->getView()->twig, $date, $timezone);
    }
}

if (!function_exists('date')) {
    /**
     * Create a DateTime instance from an input.
     */
    function date(mixed $date = null, mixed $timezone = null): \DateTimeInterface {
        global $__extension;
        return $__extension->dateFunction(\Craft::$app->getView()->twig, $date, $timezone);
    }
}

if (!function_exists('time')) {
    /**
     * Format a date as a time string.
     */
    function time(mixed $date = null, mixed $timezone = null): string {
        global $__extension;
        return $__extension->timeFilter(\Craft::$app->getView()->twig, $date, $timezone);
    }
}

if (!function_exists('datetime')) {
    /**
     * Format a date as a datetime string.
     */
    function datetime(mixed $date = null, mixed $timezone = null): string {
        global $__extension;
        return $__extension->datetimeFilter(\Craft::$app->getView()->twig, $date, $timezone);
    }
}
