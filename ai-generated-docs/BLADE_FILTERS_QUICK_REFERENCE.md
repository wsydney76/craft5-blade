# Blade Filters Quick Reference

__Note: This document is AI generated, unedited, not systematically tested. So may be correct or not.__

A quick reference guide for all Blade filter functions available in the plugin.

## String Functions

### Case Conversion
```
ascii($string)                  - Convert to ASCII
camel($string)                  - Convert to camelCase
kebab($string)                  - Convert to kebab-case
pascal($string)                 - Convert to PascalCase
snake($string)                  - Convert to snake_case
```

### String Manipulation
```
lcfirst($string)                - Lowercase first character
ucfirst($string)                - Uppercase first character
ucwords($string)                - Uppercase each word
truncate($string, $length)      - Truncate without breaking words
widont($string)                 - Remove widows (add nbsp before last word)
```

## Array/Collection Functions

### Filtering
```
filter($array, $callback)       - Filter array with callback function
find($array, $key, $value)      - Find first matching element
where($array, $key, $value)     - Filter elements matching condition
firstWhere($array, $key, $value) - Get first matching element
contains($needle, $array)       - Check if array contains value
```

### Transformation
```
map($array, $callback)          - Transform array with callback
reduce($array, $callback)       - Reduce to single value
flatten($array)                 - Flatten multidimensional array
column($array, $key)            - Extract column from array
index($array, $key)             - Build indexed map
group($array, $key)             - Group by key
multisort($array, $keys)        - Sort by multiple keys
sort($array, $key)              - Sort array
```

### Mutation
```
append($value, $append)         - Append value
prepend($value, $prepend)       - Prepend value
push($array, ...$elements)      - Push elements to end
unshift($array, ...$elements)   - Unshift elements to beginning
merge($array, ...$arrays)       - Merge arrays
replace($value, $search, $replace) - Replace values
without($array, ...$keys)       - Exclude keys
withoutKey($array, $value)      - Exclude value
```

### Search
```
indexOf($array, $needle)        - Get index of value
```

### Info
```
length($value)                  - Get length/count
```

## Number/Currency Functions

```
currency($value, $code)         - Format as currency (e.g., $99.99)
filesize($bytes)                - Format bytes (e.g., 1.2 MB)
number($value, $decimals)       - Format as decimal number
percentage($value, $decimals)   - Format as percentage
money($money, $locale)          - Format Money object
```

## Date/Time Functions

```
date($date, $format)            - Format date (default: Y-m-d)
time($date, $format)            - Format time (default: H:i)
datetime($date, $format)        - Format date+time (default: Y-m-d H:i:s)
atom($date)                     - Format for Atom feeds
httpdate($date)                 - Format for HTTP headers (RFC7231)
rss($date)                      - Format for RSS feeds (RFC2822)
duration($from, $to)            - Human-readable duration
timestamp($date)                - Convert to Unix timestamp
```

## HTML/Markup Functions

```
id($name)                       - Generate unique HTML ID
namespace($html, $ns)           - Namespace form input names
namespaceInputName($name, $ns)  - Namespace input name only
namespaceInputId($id, $ns)      - Namespace ID only
explodeClass($class)            - Split class string to array
explodeStyle($style)            - Split CSS style to array
address($address)               - Format Address object
markdown($text)                 - Convert Markdown to HTML
purify($html)                   - Sanitize HTML
parseAttr($string)              - Parse attribute string
parseRefs($text)                - Parse reference tags {ref:handle}
removeClass($html, $class)      - Remove class from HTML
```

## Encoding/Hashing Functions

```
hash($value)                    - Hash data
json_encode($value)             - Encode as JSON
json_decode($json)              - Decode JSON string
encenc($value)                  - Encrypt and encode
literal($value)                 - Mark as literal (no-op)
```

## Usage Examples by Category

### Working with Strings
```blade
{{ camel('hello_world') }}
{{ truncate($text, 100) }}
{!! markdown($content) !!}
```

### Working with Arrays
```blade
{{ count(where($items, 'published', true)) }}
{{ implode(', ', column($entries, 'title')) }}
{{ json_encode(map($items, fn($i) => $i->id)) }}
```

### Working with Numbers
```blade
Price: {{ currency(99.99, 'USD') }}
Size: {{ filesize(1024000) }}
Discount: {{ percentage(0.15) }}
```

### Working with Dates
```blade
Published: {{ date($entry->dateCreated, 'F j, Y') }}
{{ duration($start, $end) }} ago
```

### Working with HTML
```blade
{!! markdown($description) !!}
{{ namespace($form, 'fields') }}
ID: {{ id('my-element') }}
```

## Comparison with Twig Filters

| Twig Syntax | Blade Equivalent |
|---|---|
| `text \| camel` | `camel(text)` |
| `items \| where('status', 'active') \| column('title')` | `column(where(items, 'status', 'active'), 'title')` |
| `price \| currency('USD')` | `currency(price, 'USD')` |
| `date \| date('Y-m-d')` | `date(date, 'Y-m-d')` |
| `html \| markdown` | `markdown(html)` |
| `content \| parseRefs` | `parseRefs(content)` |

## Notes

- All functions use **PHP 8.1+ syntax** (union types, mixed type)
- Functions gracefully handle **null values** (return empty string or null)
- **Array functions** accept both regular arrays and Traversable objects
- **Date functions** accept DateTime objects or string representations
- **HTML functions** return strings that should be output with `{!! !!}` (unescaped)
- Most functions use **Craft CMS services** internally for consistency

## See Also

- `BLADE_FILTERS_MAPPING.md` - Detailed mapping to Craft CMS Twig filters
- `HELPER_FUNCTIONS_MAPPING.md` - List of other helper functions
- `README.md` - Plugin documentation

