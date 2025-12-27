<?php

use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\Html;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\models\Address;
use yii\base\InvalidArgumentException;

/**
 * Blade filter helpers - wraps Craft CMS Twig filters for use in Blade templates.
 * Provides functionality equivalent to Twig filters without Twig dependency.
 */
// String case conversion filters
if (!function_exists('ascii')) {
    function ascii(mixed $string): string {
        return StringHelper::toAscii((string)$string);
    }
}
if (!function_exists('camel')) {
    function camel(mixed $string): string {
        return StringHelper::toCamelCase((string)$string);
    }
}
if (!function_exists('kebab')) {
    function kebab(mixed $string, string $glue = '-', bool $lower = true, bool $removePunctuation = true): string {
        return StringHelper::toKebabCase((string)$string, $glue, $lower, $removePunctuation);
    }
}
if (!function_exists('pascal')) {
    function pascal(mixed $string): string {
        return StringHelper::toPascalCase((string)$string);
    }
}
if (!function_exists('snake')) {
    function snake(mixed $string): string {
        return StringHelper::toSnakeCase((string)$string);
    }
}
if (!function_exists('lcfirst')) {
    function lcfirst(mixed $string): string {
        return StringHelper::lowercaseFirst((string)$string);
    }
}
if (!function_exists('ucfirst')) {
    function ucfirst(mixed $string): string {
        return StringHelper::upperCaseFirst((string)$string);
    }
}
if (!function_exists('ucwords')) {
    function ucwords(string $string): string {
        $charset = 'UTF-8';
        if ($charset) {
            return mb_convert_case($string, MB_CASE_TITLE, $charset);
        }
        return \ucwords(strtolower($string));
    }
}
if (!function_exists('truncate')) {
    function truncate(string $string, int $length, string $suffix = '…', bool $splitSingleWord = true): string {
        if ($string === '' || $length <= 0) {
            return $string;
        }
        return StringHelper::safeTruncate($string, $length, $suffix, $splitSingleWord);
    }
}
if (!function_exists('widont')) {
    function widont(string $string): string {
        return preg_replace('/\s+(\S+)$/', '&nbsp;$1', $string);
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
        if (is_array($value)) {
            $value[] = $append;
        } else {
            $value = (string)$value . (string)$append;
        }
        return $value;
    }
}
if (!function_exists('prepend')) {
    function prepend(mixed $value, mixed $prepend): mixed {
        if (is_array($value)) {
            array_unshift($value, $prepend);
        } else {
            $value = (string)$prepend . (string)$value;
        }
        return $value;
    }
}
if (!function_exists('push')) {
    function push(array $array, mixed ...$elements): array {
        foreach ($elements as $element) {
            $array[] = $element;
        }
        return $array;
    }
}
if (!function_exists('unshift')) {
    function unshift(array $array, mixed ...$elements): array {
        array_unshift($array, ...$elements);
        return $array;
    }
}
if (!function_exists('merge')) {
    function merge(array $array, array ...$arrays): array {
        return array_merge($array, ...$arrays);
    }
}
if (!function_exists('without')) {
    function without(array $array, string|int ...$keys): array {
        foreach ($keys as $key) {
            unset($array[$key]);
        }
        return $array;
    }
}
if (!function_exists('withoutKey')) {
    function withoutKey(array $array, mixed $value): array {
        return array_filter($array, fn($v, $k) => $k !== $value, ARRAY_FILTER_USE_BOTH);
    }
}
if (!function_exists('indexOf')) {
    function indexOf(array $array, mixed $needle, bool $strict = false): int|string|null {
        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        }
        foreach ($array as $key => $value) {
            if ($strict ? $value === $needle : $value == $needle) {
                return $key;
            }
        }
        return null;
    }
}
if (!function_exists('replace')) {
    function replace(mixed $value, mixed $search, mixed $replace): mixed {
        if (is_array($value)) {
            return array_replace($value, [$search => $replace]);
        }
        return str_replace($search, $replace, (string)$value);
    }
}
if (!function_exists('group')) {
    function group(array $array, string|callable $key): array {
        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        }
        $grouped = [];
        foreach ($array as $item) {
            if (is_callable($key)) {
                $keyValue = $key($item);
            } else {
                $keyValue = is_array($item) ? ($item[$key] ?? null) : ($item->{$key} ?? null);
            }
            $grouped[$keyValue][] = $item;
        }
        return $grouped;
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
        if ($value === null || $value === '') {
            return '';
        }
        try {
            return \Craft::$app->getFormatter()->asCurrency($value, $currency, $options, $textOptions, $stripZeros);
        } catch (InvalidArgumentException) {
            return (string)$value;
        }
    }
}
if (!function_exists('filesize')) {
    function filesize(mixed $value, ?int $decimals = null, array $options = [], array $textOptions = []): string {
        if ($value === null || $value === '') {
            return '';
        }
        try {
            return \Craft::$app->getFormatter()->asShortSize($value, $decimals, $options, $textOptions);
        } catch (InvalidArgumentException) {
            return (string)$value;
        }
    }
}
if (!function_exists('number')) {
    function number(mixed $value, ?int $decimals = null, array $options = [], array $textOptions = []): string {
        if ($value === null || $value === '') {
            return '';
        }
        try {
            return \Craft::$app->getFormatter()->asDecimal($value, $decimals, $options, $textOptions);
        } catch (InvalidArgumentException) {
            return (string)$value;
        }
    }
}
if (!function_exists('percentage')) {
    function percentage(mixed $value, ?int $decimals = null, array $options = [], array $textOptions = []): string {
        if ($value === null || $value === '') {
            return '';
        }
        try {
            return \Craft::$app->getFormatter()->asPercent($value, $decimals, $options, $textOptions);
        } catch (InvalidArgumentException) {
            return (string)$value;
        }
    }
}
if (!function_exists('money')) {
    function money(mixed $money, ?string $formatLocale = null): ?string {
        if ($money === null) {
            return null;
        }
        return \craft\helpers\MoneyHelper::toString($money, $formatLocale);
    }
}
// Date/Time filters
if (!function_exists('duration')) {
    function duration(int|float|DateTime|string $from, int|float|DateTime|string|null $to = null, bool $showSeconds = false): string {
        return DateTimeHelper::humanDuration($from, $to, $showSeconds);
    }
}
if (!function_exists('timestamp')) {
    function timestamp(mixed $value): ?int {
        if ($value === null) {
            return null;
        }
        if ($value instanceof DateTime) {
            return $value->getTimestamp();
        }
        if (is_numeric($value)) {
            return (int)$value;
        }
        try {
            $date = new DateTime((string)$value);
            return $date->getTimestamp();
        } catch (\Exception) {
            return null;
        }
    }
}
// HTML/Markup filters
if (!function_exists('address')) {
    function address(?Address $address, array $options = []): string {
        if ($address === null) {
            return '';
        }
        return \Craft::$app->getAddresses()->formatAddress($address, $options);
    }
}
if (!function_exists('explodeClass')) {
    function explodeClass(mixed $input): array {
        return Html::explodeClass($input);
    }
}
if (!function_exists('explodeStyle')) {
    function explodeStyle(mixed $input): array {
        return Html::explodeStyle($input);
    }
}
if (!function_exists('id')) {
    function id(string $name): string {
        return Html::id($name);
    }
}
if (!function_exists('namespaceInputs')) {
    function namespaceInputs(string $html, string $namespace = ''): string {
        return \Craft::$app->getView()->namespaceInputs($html, $namespace);
    }
}
if (!function_exists('namespaceInputName')) {
    function namespaceInputName(string $inputName, string $namespace = ''): string {
        return \Craft::$app->getView()->namespaceInputName($inputName, $namespace);
    }
}
if (!function_exists('namespaceInputId')) {
    function namespaceInputId(string $inputId, string $namespace = ''): string {
        return \Craft::$app->getView()->namespaceInputId($inputId, $namespace);
    }
}
if (!function_exists('markdown')) {
    function markdown(string $markdown, bool $inline = false): string {
        if (!$markdown) {
            return '';
        }
        if ($inline) {
            return \craft\helpers\Markdown::processParagraph($markdown);
        }
        return \craft\helpers\Markdown::process($markdown);
    }
}
if (!function_exists('purify')) {
    function purify(string $html, ?array $config = null): string {
        if (!$html) {
            return '';
        }
        return \Craft::$app->getContent()->filterHtml($html, $config);
    }
}
if (!function_exists('removeClass')) {
    function removeClass(string $html, string|array $class): string {
        if (!is_array($class)) {
            $class = (array)$class;
        }
        try {
            return Html::modifyTagAttributes($html, ['class' => static function($classes) use ($class) {
                return array_diff($classes, $class);
            }]);
        } catch (InvalidArgumentException) {
            return $html;
        }
    }
}
if (!function_exists('parseAttr')) {
    function parseAttr(string $attrString): array {
        $attributes = [];
        if (preg_match_all('/(\w+)=["\']?([^"\'\s]+)["\']?/', $attrString, $matches)) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                $attributes[$matches[1][$i]] = $matches[2][$i];
            }
        }
        return $attributes;
    }
}
if (!function_exists('parseRefs')) {
    function parseRefs(string $string): string {
        return \Craft::$app->getElements()->parseRefs($string);
    }
}
// Encoding/Hashing filters
if (!function_exists('hash')) {
    function hash(mixed $value): string {
        return \Craft::$app->getSecurity()->hashData((string)$value);
    }
}
if (!function_exists('json_encode')) {
    function json_encode(mixed $value, int $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APO | JSON_HEX_QUOT, int $depth = 512): string {
        return \json_encode($value, $options, $depth);
    }
}
if (!function_exists('json_decode')) {
    function json_decode(string $json, bool $assoc = true, int $depth = 512, int $options = 0): mixed {
        return Json::decode($json, $assoc, $depth, $options);
    }
}
if (!function_exists('encenc')) {
    function encenc(mixed $value): string {
        return \Craft::$app->getSecurity()->encryptByPassword((string)$value, \Craft::$app->getConfig()->getGeneral()->securityKey);
    }
}
if (!function_exists('literal')) {
    function literal(mixed $string): string {
        return (string)$string;
    }
}
// Complex filters that work with closures/callables
if (!function_exists('find')) {
    function find(array $array, callable|string|null $key = null, mixed $value = null): mixed {
        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        }
        if ($key === null) {
            return reset($array);
        }
        if (is_callable($key)) {
            foreach ($array as $item) {
                if ($key($item)) {
                    return $item;
                }
            }
        } else {
            foreach ($array as $item) {
                $itemValue = is_array($item) ? ($item[$key] ?? null) : ($item->{$key} ?? null);
                if ($itemValue == $value) {
                    return $item;
                }
            }
        }
        return null;
    }
}
if (!function_exists('filter')) {
    function filter(array $array, callable $callback): array {
        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        }
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }
}
if (!function_exists('length')) {
    function length(mixed $value): int {
        if (is_array($value) || $value instanceof \Countable) {
            return count($value);
        }
        if ($value instanceof \Traversable) {
            return iterator_count($value);
        }
        return strlen((string)$value);
    }
}
if (!function_exists('sort')) {
    function sort(array $array, callable|string|null $arrow = null): array {
        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        }
        if ($arrow === null) {
            asort($array);
        } elseif (is_callable($arrow)) {
            usort($array, function($a, $b) use ($arrow) {
                $aVal = $arrow($a);
                $bVal = $arrow($b);
                return $aVal <=> $bVal;
            });
        } else {
            usort($array, function($a, $b) use ($arrow) {
                $aVal = is_array($a) ? ($a[$arrow] ?? null) : ($a->{$arrow} ?? null);
                $bVal = is_array($b) ? ($b[$arrow] ?? null) : ($b->{$arrow} ?? null);
                return $aVal <=> $bVal;
            });
        }
        return $array;
    }
}
if (!function_exists('reduce')) {
    function reduce(array $array, callable $arrow, mixed $initial = null): mixed {
        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        }
        return array_reduce($array, $arrow, $initial);
    }
}
if (!function_exists('map')) {
    function map(array $array, callable $arrow): array {
        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        }
        return array_map($arrow, $array);
    }
}
// Special/Output filters for date formatting
if (!function_exists('atom')) {
    function atom(mixed $date): string {
        if ($date instanceof DateTime) {
            return $date->format(DateTime::ATOM);
        }
        try {
            $dateTime = new DateTime((string)$date);
            return $dateTime->format(DateTime::ATOM);
        } catch (\Exception) {
            return '';
        }
    }
}
if (!function_exists('httpdate')) {
    function httpdate(mixed $date): string {
        if ($date instanceof DateTime) {
            return $date->format(DateTime::RFC7231);
        }
        try {
            $dateTime = new DateTime((string)$date);
            return $dateTime->format(DateTime::RFC7231);
        } catch (\Exception) {
            return '';
        }
    }
}
if (!function_exists('rss')) {
    function rss(mixed $date): string {
        if ($date instanceof DateTime) {
            return $date->format(DateTime::RFC2822);
        }
        try {
            $dateTime = new DateTime((string)$date);
            return $dateTime->format(DateTime::RFC2822);
        } catch (\Exception) {
            return '';
        }
    }
}
if (!function_exists('date')) {
    function date(mixed $date, string $format = 'Y-m-d', ?string $timezone = null): string {
        if ($date instanceof DateTime) {
            if ($timezone) {
                $date->setTimezone(new \DateTimeZone($timezone));
            }
            return $date->format($format);
        }
        try {
            $dateTime = new DateTime((string)$date);
            if ($timezone) {
                $dateTime->setTimezone(new \DateTimeZone($timezone));
            }
            return $dateTime->format($format);
        } catch (\Exception) {
            return '';
        }
    }
}
if (!function_exists('time')) {
    function time(mixed $date, string $format = 'H:i', ?string $timezone = null): string {
        if ($date instanceof DateTime) {
            if ($timezone) {
                $date->setTimezone(new \DateTimeZone($timezone));
            }
            return $date->format($format);
        }
        try {
            $dateTime = new DateTime((string)$date);
            if ($timezone) {
                $dateTime->setTimezone(new \DateTimeZone($timezone));
            }
            return $dateTime->format($format);
        } catch (\Exception) {
            return '';
        }
    }
}
if (!function_exists('datetime')) {
    function datetime(mixed $date, string $format = 'Y-m-d H:i:s', ?string $timezone = null): string {
        if ($date instanceof DateTime) {
            if ($timezone) {
                $date->setTimezone(new \DateTimeZone($timezone));
            }
            return $date->format($format);
        }
        try {
            $dateTime = new DateTime((string)$date);
            if ($timezone) {
                $dateTime->setTimezone(new \DateTimeZone($timezone));
            }
            return $dateTime->format($format);
        } catch (\Exception) {
            return '';
        }
    }
}
