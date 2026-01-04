# Blade Filters Mapping

__Note: This document is AI generated, unedited, untested. So may be correct or not.__

This document maps each filter function in `BladeFilters.php` to the corresponding Twig filter from `craft\web\twig\Extension`.

## String Case Conversion Filters

| Filter Function | Source Twig Filter | Wrapped Static Class Call / Method | Notes |
|----------------|-------------------|---------------------------------------|-------|
| `ascii()` | `ascii` | `StringHelper::toAscii()` | Converts string to ASCII |
| `camel()` | `camel` | `StringHelper::toCamelCase()` | camelCases a string |
| `kebab()` | `kebab` | `StringHelper::toKebabCase()` | kebab-cases a string |
| `pascal()` | `pascal` | `StringHelper::toPascalCase()` | PascalCases a string |
| `snake()` | `snake` | `StringHelper::toSnakeCase()` | snake_cases a string |
| `lcfirst()` | `lcfirst` | `StringHelper::lowercaseFirst()` | Lowercases first character |
| `ucfirst()` | `ucfirst` | `StringHelper::upperCaseFirst()` | Uppercases first character (deprecated) |
| `ucwords()` | `ucwords` | `mb_convert_case()` | Uppercases first character of each word (deprecated) |
| `truncate()` | `truncate` | `StringHelper::safeTruncate()` | Truncates string without splitting words |
| `widont()` | `widont` | `preg_replace()` | Removes widows by adding non-breaking space |

## Array/Collection Filters

| Filter Function | Source Twig Filter | Wrapped Static Class Call / Method | Notes |
|----------------|-------------------|---------------------------------------|-------|
| `column()` | `column` | `ArrayHelper::getColumn()` | Returns values of specified column |
| `contains()` | `contains` | `ArrayHelper::contains()` | Checks if array contains value |
| `firstWhere()` | `firstWhere` | `ArrayHelper::firstWhere()` | Returns first matching element |
| `index()` | `index` | `ArrayHelper::index()` | Builds key-value map from array |
| `where()` | `where` | `ArrayHelper::where()` | Filters array by condition |
| `flatten()` | `flatten` | Custom implementation | Flattens multidimensional array |
| `append()` | `append` | Custom implementation | Appends to string or array |
| `prepend()` | `prepend` | Custom implementation | Prepends to string or array |
| `push()` | `push` | Custom implementation | Pushes elements to array |
| `unshift()` | `unshift` | Custom implementation | Prepends elements to array |
| `merge()` | `merge` | `array_merge()` | Merges arrays |
| `without()` | `without` | Custom implementation | Returns array without specified keys |
| `withoutKey()` | `withoutKey` | `array_filter()` | Returns array without specified value |
| `indexOf()` | `indexOf` | Custom implementation | Returns index of value in array |
| `replace()` | `replace` | `str_replace()` / `array_replace()` | Replaces values in string or array |
| `group()` | `group` | Custom implementation | Groups array elements by key |
| `multisort()` | `multisort` | `usort()` | Sorts array by multiple keys |

## Number/Currency Filters

| Filter Function | Source Twig Filter | Wrapped Static Class Call / Method | Notes |
|----------------|-------------------|---------------------------------------|-------|
| `currency()` | `currency` | `Formatter::asCurrency()` | Formats value as currency |
| `filesize()` | `filesize` | `Formatter::asShortSize()` | Formats bytes as human-readable size |
| `number()` | `number` | `Formatter::asDecimal()` | Formats value as decimal number |
| `percentage()` | `percentage` | `Formatter::asPercent()` | Formats value as percentage |
| `money()` | `money` | `MoneyHelper::toString()` | Outputs Money object value |

## Date/Time Filters

| Filter Function | Source Twig Filter | Wrapped Static Class Call / Method | Notes |
|----------------|-------------------|---------------------------------------|-------|
| `duration()` | `duration` | `DateTimeHelper::humanDuration()` | Returns human-readable duration |
| `timestamp()` | `timestamp` | Custom implementation | Converts date to timestamp |
| `atom()` | `atom` | `DateTime::format()` | Formats date for Atom feed |
| `httpdate()` | `httpdate` | `DateTime::format()` | Formats date for HTTP headers |
| `rss()` | `rss` | `DateTime::format()` | Formats date for RSS feed |
| `date()` | `date` | `DateTime::format()` | Formats date |
| `time()` | `time` | `DateTime::format()` | Formats time |
| `datetime()` | `datetime` | `DateTime::format()` | Formats date and time |

## HTML/Markup Filters

| Filter Function | Source Twig Filter | Wrapped Static Class Call / Method | Notes |
|----------------|-------------------|---------------------------------------|-------|
| `address()` | `address` | `Addresses::formatAddress()` | Formats Address object |
| `attrFilter()` | `attr` | `Html::renderTagAttributes()` | Renders HTML attributes |
| `explodeClass()` | `explodeClass` | `Html::explodeClass()` | Splits space-separated class string |
| `explodeStyle()` | `explodeStyle` | `Html::explodeStyle()` | Splits CSS style string |
| `id()` | `id` | `Html::id()` | Generates unique HTML ID |
| `namespace()` | `namespace` | `View::namespaceInputs()` | Namespaces form input names/IDs |
| `namespaceInputName()` | `namespaceInputName` | `View::namespaceInputName()` | Namespaces input name |
| `namespaceInputId()` | `namespaceInputId` | `View::namespaceInputId()` | Namespaces input ID |
| `markdown()` | `markdown` / `md` | `Markdown::process()` | Converts Markdown to HTML |
| `purify()` | `purify` | `Content::filterHtml()` | Sanitizes HTML with HTML Purifier |
| `removeClass()` | `removeClass` | `Html::modifyTagAttributes()` | Removes class from HTML element |
| `parseAttr()` | `parseAttr` | Custom implementation | Parses attribute string to array |
| `parseRefs()` | `parseRefs` | `Elements::parseRefs()` | Parses reference tags |

## Encoding/Hashing Filters

| Filter Function | Source Twig Filter | Wrapped Static Class Call / Method | Notes |
|----------------|-------------------|---------------------------------------|-------|
| `hash()` | `hash` | `Security::hashData()` | Hashes data |
| `json_encode()` | `json_encode` | `json_encode()` | Encodes value as JSON |
| `json_decode()` | `json_decode` | `Json::decode()` | Decodes JSON string |
| `encenc()` | `encenc` | `Security::encryptByPassword()` | Encrypts and encodes value |
| `literal()` | `literal` | Custom implementation | Marks string as literal output |

## Advanced Array Filters

| Filter Function | Source Twig Filter | Wrapped Static Class Call / Method | Notes |
|----------------|-------------------|---------------------------------------|-------|
| `find()` | `find` | Custom implementation | Finds first matching element |
| `filter()` | `filter` | `array_filter()` | Filters array with callback |
| `length()` | `length` | `count()` / `strlen()` | Returns length/count |
| `sort()` | `sort` | `asort()` / `usort()` | Sorts array |
| `reduce()` | `reduce` | `array_reduce()` | Reduces array to single value |
| `map()` | `map` | `array_map()` | Maps array with callback |

## Skipped Filters

The following Twig filters were **not** implemented because they are either:
- PHP native functions already available (base64_decode, base64_encode, boolean, integer, float, string, diff, intersect, unique, values)
- Direct Craft CMS class methods that can be called directly (column, explodeClass, explodeStyle, id, firstWhere, flatten, index, json_decode, where)
- Translation filters (t, translate) - use the `__()` function from BladeHelpers.php instead
- Deprecated filters without functional equivalents

| Skipped Filter | Reason |
|---|---|
| `base64_decode` | PHP native function |
| `base64_encode` | PHP native function |
| `boolean` / `boolval` | PHP native function |
| `integer` / `intval` | PHP native function |
| `float` / `floatval` | PHP native function |
| `string` / `strval` | PHP native function |
| `diff` / `array_diff` | PHP native function |
| `intersect` / `array_intersect` | PHP native function |
| `unique` / `array_unique` | PHP native function |
| `values` / `array_values` | PHP native function |
| `t` | Use `__()` helper function from BladeHelpers.php instead |
| `translate` | Use `__()` helper function from BladeHelpers.php instead |
| `filterByValue` | Deprecated (use `where` instead) |

## Implementation Notes

1. **Twig Environment Dependency**: Some Twig filters require the `TwigEnvironment` parameter (atom, date, datetime, filter, find, httpdate, length, map, reduce, rss, sort, time). These have been adapted to work without Twig by using standard PHP functions or simplified implementations.

2. **Array/Iterable Support**: All array filters accept both arrays and iterables (Traversable objects), converting them to arrays when necessary.

3. **Namespace Filter**: Both `namespace` and `ns` Twig filters map to the same `namespace()` function.

4. **Markdown Filter**: Both `markdown` and `md` Twig filters map to the same `markdown()` function.

5. **Case Conversions**: All string case conversion filters use Craft's `StringHelper` class for consistent behavior.

6. **Error Handling**: Filters that may throw exceptions (date parsing, JSON encoding, formatting) include try-catch blocks to gracefully handle errors.

7. **HTML Safety**: Filters that output HTML markup (markdown, purify, parseRefs, removeClass) return strings that should be treated as safe HTML when used in Blade templates. Use `{!! $content !!}` instead of `{{ $content }}` to output them without escaping.

