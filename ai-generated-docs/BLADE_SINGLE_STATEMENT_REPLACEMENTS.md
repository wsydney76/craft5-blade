# Blade: Single-statement replacements (helpers + filters)

__Note: This document is AI generated, unedited, not systematically tested. So may be correct or not.__

This doc identifies functions in:

- `blade/src/support/BladeHelpers.php`
- `blade/src/support/BladeFilters.php`

…that can effectively be replaced in templates by a **single PHP statement** (i.e. one expression / one call).

## What qualifies

A function is included when its body is essentially a direct call/chain like:

- `return SomeClass::method(...);`
- `return Craft::$app->getService()->method(...);`

…and the wrapper does **not** add extra semantics (special parameter reshaping, error handling, logging, deprecations, etc.).

## What is intentionally excluded

Most functions in these files delegate to Craft’s Twig extension via:

- `global $__extension; return $__extension->...`

Those are **not** listed as “single-statement replacements” because the *equivalent behavior is Craft’s Twig Extension method itself*, and replacing it inline would either:

- require access to the same `$__extension` instance, or
- subtly change behavior (localization, encoding, environment-dependent logic, etc.).

They are listed in a separate section as “Not safely replaceable”.

---

# BladeHelpers.php — single-statement replacements

## URL helpers (Craft `UrlHelper`)

| Helper | Replacement statement | Notes |
|---|---|---|
| `actionUrl($path = '', $params = [], $scheme = null)` | `craft\helpers\UrlHelper::actionUrl($path, $params, $scheme)` | Returns a string URL. |
| `cpUrl($path = '', $params = [], $scheme = null)` | `craft\helpers\UrlHelper::cpUrl($path, $params, $scheme)` | Returns a string URL. |
| `siteUrl($path = '', $params = [], $scheme = null, $siteId = null)` | `craft\helpers\UrlHelper::siteUrl($path, $params, $scheme, $siteId)` | Returns a string URL. |
| `url($path = '', $params = [], $scheme = null)` | `craft\helpers\UrlHelper::url($path, $params, $scheme)` | Returns a string URL. |
| `encodeUrl($url)` | `craft\helpers\UrlHelper::encodeUrl($url)` | Encodes a URL for use in an HTML attribute. |

## Craft core convenience

| Helper | Replacement statement | Notes |
|---|---|---|
| `alias($alias)` | `Craft::getAlias($alias)` | Craft must be bootstrapped (true in templates). |
| `configure($object, $properties)` | `Craft::configure($object, $properties)` | Mutates and returns `$object`. |
| `create($config)` | `Craft::createObject($config)` | Returns created object. |

## Element authorization (Craft Elements service)

| Helper | Replacement statement | Notes |
|---|---|---|
| `canCreateDrafts($element, $user = null)` | `Craft::$app->getElements()->canCreateDrafts($element, $user)` | Requires `craft\base\ElementInterface`. |
| `canDelete($element, $user = null)` | `Craft::$app->getElements()->canDelete($element, $user)` |  |
| `canDeleteForSite($element, $user = null)` | `Craft::$app->getElements()->canDeleteForSite($element, $user)` |  |
| `canDuplicate($element, $user = null)` | `Craft::$app->getElements()->canDuplicate($element, $user)` |  |
| `canSave($element, $user = null)` | `Craft::$app->getElements()->canSave($element, $user)` |  |
| `canView($element, $user = null)` | `Craft::$app->getElements()->canView($element, $user)` |  |

## HTML + form helpers (Craft `Html`)

| Helper | Replacement statement | Notes |
|---|---|---|
| `actionInput($action, $params = [])` | `craft\helpers\Html::actionInput($action, $params)` | Outputs HTML; use `{!! !!}` in Blade. |
| `attr($attributes)` | `craft\helpers\Html::renderTagAttributes($attributes)` | Outputs HTML attributes; use `{!! !!}`. |
| `csrfInput()` | `craft\helpers\Html::csrfInput()` | Outputs HTML; use `{!! !!}`. |
| `failMessageInput($message = null)` | `craft\helpers\Html::failMessageInput($message)` | Outputs HTML; use `{!! !!}`. |
| `hiddenInput($name, $value = null, $options = [])` | `craft\helpers\Html::hiddenInput($name, $value, $options)` | Outputs HTML; use `{!! !!}`. |
| `input($type, $name = null, $value = null, $options = [])` | `craft\helpers\Html::input($type, $name, $value, $options)` | Outputs HTML; use `{!! !!}`. |
| `ol($items, $attributes = [])` | `craft\helpers\Html::ol($items, $attributes)` | Outputs HTML; use `{!! !!}`. |
| `redirectInput($url)` | `craft\helpers\Html::redirectInput($url)` | Outputs HTML; use `{!! !!}`. |
| `successMessageInput($message = null)` | `craft\helpers\Html::successMessageInput($message)` | Outputs HTML; use `{!! !!}`. |

## View lifecycle helpers (Craft View)

| Helper | Replacement statement | Notes |
|---|---|---|
| `head()` | `Craft::$app->getView()->head()` | Side effects (registers head HTML). Use in layouts. |
| `beginBody()` | `Craft::$app->getView()->beginBody()` | Side effects (opens body). |
| `endBody()` | `Craft::$app->getView()->endBody()` | Side effects (closes body). |

## SQL expression helper

| Helper | Replacement statement | Notes |
|---|---|---|
| `expression($expression, $params = [], $config = [])` | `new yii\db\Expression($expression, $params, $config)` | Requires Yii class; in templates you may prefer `expression()` for readability. |

---

# BladeFilters.php — single-statement replacements

## Pure PHP / Craft service calls

| Filter | Replacement statement | Notes |
|---|---|---|
| `ascii($string)` | `craft\helpers\StringHelper::toAscii((string)$string)` | This wrapper already is a single statement. |
| `hash($value)` | `Craft::$app->getSecurity()->hashData((string)$value)` | Not a password hash; it’s signed data hashing (Craft security service). |

> Note: the other filters in `BladeFilters.php` delegate to Craft’s Twig extension (`$__extension`). They’re listed below as “not safely replaceable”.

---

# Not safely replaceable by a single statement (delegates to Craft Twig Extension)

These functions all call `global $__extension;` and then delegate to `craft\web\twig\Extension` methods (often also passing the Twig environment). Replacing them inline would require the *same* `Extension` instance and environment, and may change behavior.

## BladeHelpers.php

- `app()` (Laravel container semantics)
- `clone_var()`
- `collect()`
- `dataUrl()`
- `dump()`
- `entryType()`
- `fieldValueSql()`
- `gql()`
- `plugin()`
- `seq()` (also adds `$next` branching)
- `shuffle_arr()`
- `svg()` (adds deprecation logging + optional class injection)
- `tag()` (delegates to Twig extension)
- `__()` / `t()` (adds category normalization + error handling)

## BladeFilters.php

String/case:
- `camel()`, `kebab()`, `pascal()`, `snake()`, `lcfirst()`, `ucfirst()`, `ucwords()`, `truncate()`, `widont()`

Arrays/collections:
- `append()`, `prepend()`, `push()`, `unshift()`, `merge()`, `without()`, `withoutKey()`, `indexOf()`, `replace()`, `group()`

Numbers/currency:
- `currency()`, `filesize()`, `number()`, `percentage()`, `money()`

Dates:
- `timestamp()`, `atom()`, `httpdate()`, `rss()`, `date()`, `time()`, `datetime()`

HTML/markup:
- `address()`, `markdown()`, `purify()`, `removeClass()`, `parseAttr()`, `parseRefs()`

Encoding:
- `encenc()`

Callable/arrow-based:
- `find()`, `filter()`, `length()`, `sort()`, `reduce()`, `map()`

---

# Notes / edge cases

- Some wrappers are “one statement” but **not equivalent** if rewritten naïvely (e.g. `__()` is not the same as `Craft::t()` with the same positional args).
- For HTML-returning helpers, Blade should generally output with `{!! ... !!}` (or your project’s safe HTML strategy).
- If you ever want to truly remove wrappers, prefer replacing calls in templates via a codemod and run your template test suite; do not change behavior silently.

