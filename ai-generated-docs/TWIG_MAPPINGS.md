# Twig 3.x → Laravel/Blade Equivalency Map (Functions, Filters, Tests)

__Note: This document is AI generated, unedited, untested. So may be correct or not.__

This document maps **Twig 3.x built-ins** (functions, filters, tests) to the closest **Laravel / Blade** equivalents (Blade directives, Laravel helpers, Illuminate\Support utilities, and standard PHP).
Twig reference pages: Functions, Filters, Tests. ([Twig][1])

---

## 1) Twig Functions → Blade / Laravel

Twig function reference: ([Twig][1])

| Twig function               | Closest Laravel / Blade equivalent                             | Notes / typical implementation                                                                   |
| --------------------------- | -------------------------------------------------------------- | ------------------------------------------------------------------------------------------------ |
| `attribute(obj, 'prop')`    | PHP access; `data_get()`                                       | Dynamic paths: `data_get($obj, $path)` (dot notation).                                           |
| `block('name')`             | `@yield('name')` / `@section('name')`                          | Layout/section inheritance parallels Twig blocks.                                                |
| `constant('FOO')`           | PHP `constant('FOO')`                                          | Same semantics.                                                                                  |
| `cycle(values, i)`          | Modulo; `$loop`                                                | `{{ $values[$i % count($values)] }}` or `$loop->index/$loop->iteration` inside `@foreach`.       |
| `date(...)`                 | Carbon / `now()` / `Carbon::parse()`                           | Laravel standard is Carbon for parsing/formatting.                                               |
| `dump(...)`                 | `@dump(...)` / `dd()`                                          | Blade includes debug helpers.                                                                    |
| `enum('App\\Enum')`         | PHP native enums                                               | Usually passed from controller; PHP 8.1+ feature.                                                |
| `enum_cases('App\\Enum')`   | `MyEnum::cases()`                                              | Native `cases()` on enums (PHP 8.1+).                                                            |
| `html_classes(...)`         | `@class([...])`                                                | Blade conditional class construction.                                                            |
| `html_cva(...)`             | `@class([...])`, `@style([...])`, component attribute patterns | Closest is Blade attribute/merge patterns; implement a helper if you need a “CVA”-style builder. |
| `include('view', vars)`     | `@include('view', [...])`                                      | Direct mapping.                                                                                  |
| `max(a, b, ...)`            | PHP `max()` / Collection `->max()`                             | Choose by data type.                                                                             |
| `min(a, b, ...)`            | PHP `min()` / Collection `->min()`                             | —                                                                                                |
| `parent()`                  | `@parent`                                                      | Used inside a section definition.                                                                |
| `random(seq)`               | `Arr::random()` / Collection `->random()`                      | Prefer `collect($seq)->random()`.                                                                |
| `range(low, high, step?)`   | PHP `range()`                                                  | Direct mapping.                                                                                  |
| `source('template')`        | No direct Blade equivalent                                     | Reading template source is atypical in Blade views.                                              |
| `country_timezones(...)`    | Intl / `DateTimeZone`                                          | Laravel has no built-in “country → timezones” resolver.                                          |
| `country_names(...)`        | Intl tooling (often `symfony/intl`)                            | Not built-in to Laravel core.                                                                    |
| `currency_names(...)`       | Intl tooling                                                   | —                                                                                                |
| `language_names(...)`       | Intl tooling                                                   | —                                                                                                |
| `locale_names(...)`         | Intl tooling                                                   | —                                                                                                |
| `script_names(...)`         | Intl tooling                                                   | —                                                                                                |
| `timezone_names(...)`       | `DateTimeZone::listIdentifiers()`                              | Add localization separately if required.                                                         |
| `template_from_string(str)` | No direct Blade equivalent                                     | Blade is not designed for ad-hoc runtime compilation/rendering of arbitrary strings.             |

---

## 2) Twig Filters → Blade / Laravel

Twig filter reference: ([Twig][1])

> Blade does not have a native “pipe filter” operator; you typically replace filters with PHP functions, Carbon, Collections, and `Str` / `Arr`.

| Twig filter        | Closest Laravel / Blade equivalent                       | Notes / typical implementation                              |
| ------------------ | -------------------------------------------------------- | ----------------------------------------------------------- |
| `abs`              | `abs()`                                                  | —                                                           |
| `batch`            | `collect($x)->chunk($n)`                                 | —                                                           |
| `capitalize`       | `Str::ucfirst()`                                         | Adjust casing rules as needed for your locale/requirements. |
| `column`           | `array_column()` / `collect()->pluck()`                  | —                                                           |
| `convert_encoding` | `mb_convert_encoding()`                                  | Requires mbstring.                                          |
| `country_name`     | Intl tooling                                             | No direct Laravel core equivalent.                          |
| `currency_name`    | Intl tooling                                             | —                                                           |
| `currency_symbol`  | PHP intl `NumberFormatter`                               | —                                                           |
| `data_uri`         | Manual base64 data URI                                   | Build `data:<mime>;base64,<payload>`.                       |
| `date`             | Carbon `->format()`                                      | `{{ $dt->format('Y-m-d') }}`                                |
| `date_modify`      | Carbon `->modify()` / `->add…()`                         | `modify('+3 days')` or `addDays(3)`.                        |
| `default`          | `??`                                                     | `{{ $v ?? 'fallback' }}`                                    |
| `escape`           | `{{ }}`                                                  | Blade escapes by default.                                   |
| `filter`           | `collect()->filter()`                                    | Twig’s filter filter ≈ collection filtering.                |
| `find`             | `collect()->first(fn...)`                                | First matching element.                                     |
| `first`            | `collect()->first()`                                     | —                                                           |
| `format`           | `sprintf()`                                              | —                                                           |
| `format_currency`  | PHP intl `NumberFormatter`                               | —                                                           |
| `format_date`      | Carbon localized formatting                              | E.g. `translatedFormat(...)` depending on setup.            |
| `format_datetime`  | Carbon localized formatting                              | —                                                           |
| `format_number`    | PHP intl `NumberFormatter`                               | —                                                           |
| `format_time`      | Carbon localized formatting                              | —                                                           |
| `html_to_markdown` | External library                                         | Commonly solved via a package (not Laravel core).           |
| `inline_css`       | External library                                         | Often mail-focused (CSS inlining).                          |
| `inky_to_html`     | External tooling                                         | Typically mail templating tooling.                          |
| `invoke`           | PHP callables                                            | `{{ $fn($arg) }}` when `$fn` is callable.                   |
| `join`             | `implode()` / collection join helpers                    | —                                                           |
| `json_encode`      | `@json($value)` / `json_encode()`                        | Blade’s `@json` is typical for JS contexts.                 |
| `keys`             | `array_keys()` / `collect()->keys()`                     | —                                                           |
| `language_name`    | Intl tooling                                             | —                                                           |
| `last`             | `collect()->last()`                                      | —                                                           |
| `length`           | `count()` / `Str::length()`                              | Use based on array vs string.                               |
| `locale_name`      | Intl tooling                                             | —                                                           |
| `lower`            | `Str::lower()`                                           | —                                                           |
| `map`              | `collect()->map()`                                       | —                                                           |
| `markdown_to_html` | External library                                         | CommonMark packages are typical.                            |
| `merge`            | `array_merge()` / `collect()->merge()`                   | —                                                           |
| `nl2br`            | `nl2br()`                                                | Usually pair with escaping/encoding decisions.              |
| `number_format`    | `number_format()`                                        | —                                                           |
| `plural`           | `Str::plural()`                                          | —                                                           |
| `raw`              | `{!! !!}`                                                | Unescaped output; use sparingly and intentionally.          |
| `reduce`           | `collect()->reduce()`                                    | —                                                           |
| `replace`          | `str_replace()` / `preg_replace()` / `Str` helpers       | Choose plain vs regex.                                      |
| `reverse`          | `array_reverse()` / `collect()->reverse()`               | —                                                           |
| `round`            | `round()`                                                | —                                                           |
| `shuffle`          | `collect()->shuffle()`                                   | Prefer collection approach for readability.                 |
| `singular`         | `Str::singular()`                                        | —                                                           |
| `slice`            | `array_slice()` / `Str::substr()` / `collect()->slice()` | Choose by data type.                                        |
| `slug`             | `Str::slug()`                                            | —                                                           |
| `sort`             | `sort()` / `collect()->sort()` / `->sortBy()`            | Prefer `sortBy()` for key-based sorting.                    |
| `spaceless`        | Not native                                               | Often replaced by HTML minification tooling outside views.  |
| `split`            | `explode()` / `preg_split()`                             | Choose plain vs regex.                                      |
| `striptags`        | `strip_tags()`                                           | —                                                           |
| `timezone_name`    | `DateTimeZone` + localization                            | No direct Laravel helper.                                   |
| `title`            | `Str::title()`                                           | —                                                           |
| `trim`             | `trim()`                                                 | —                                                           |
| `u`                | `Str::of($s)`                                            | Fluent string operations are the closest analogue.          |
| `upper`            | `Str::upper()`                                           | —                                                           |
| `url_encode`       | `urlencode()` / `rawurlencode()`                         | Choose based on encoding rules required.                    |

---

## 3) Twig Tests → Blade / Laravel

Twig tests index (built-ins in 3.x): `constant`, `defined`, `divisible by`, `empty`, `even`, `iterable`, `null` (alias `none`), `odd`, `same as`. ([Twig][2])

| Twig test (syntax: `x is test(...)`) | Meaning in Twig                                                                                                                              | Closest Laravel / Blade equivalent                                                                                                                                      |   |              |   |                              |   |                                                                              |
| ------------------------------------ | -------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- | - | ------------ | - | ---------------------------- | - | ---------------------------------------------------------------------------- |
| `constant('Class::CONST')`           | Checks if value exactly equals a constant. ([Twig][3])                                                                                       | `($value === \App\Thing::CONST)` or `($value === constant('App\\\\Thing::CONST'))`                                                                                      |   |              |   |                              |   |                                                                              |
| `defined`                            | Checks whether a variable/attribute exists in the context (especially relevant with strict variables). ([Twig][4])                           | Prefer designing views so variables always exist; otherwise: `isset($var)` / `array_key_exists('k', $arr)` / `data_get($obj,'path') !== null` (depending on semantics). |   |              |   |                              |   |                                                                              |
| `divisible by(n)`                    | Checks divisibility. ([Twig][5])                                                                                                             | `($x % $n) === 0` (often with `$loop->iteration` / `$loop->index` in `@foreach`).                                                                                       |   |              |   |                              |   |                                                                              |
| `empty`                              | True for empty string/array/map, or exactly `false` or `null` (Twig’s definition is broader than PHP’s `empty()` in some cases). ([Twig][6]) | Usually `blank($v)` / `filled($v)` (Laravel helpers) or explicit checks: `is_null($v)                                                                                   |   | $v === false |   | (is_string($v) && $v === '') |   | (is_countable($v) && count($v)===0)` depending on required parity with Twig. |
| `even`                               | Number is even. ([Twig][7])                                                                                                                  | `($x % 2) === 0`                                                                                                                                                        |   |              |   |                              |   |                                                                              |
| `iterable`                           | Value is array or Traversable. ([Twig][8])                                                                                                   | `is_iterable($x)` (PHP 7.1+)                                                                                                                                            |   |              |   |                              |   |                                                                              |
| `null` / `none`                      | Value is null; `none` is an alias. ([Twig][9])                                                                                               | `is_null($x)` or `$x === null`                                                                                                                                          |   |              |   |                              |   |                                                                              |
| `odd`                                | Number is odd. ([Twig][10])                                                                                                                  | `($x % 2) !== 0`                                                                                                                                                        |   |              |   |                              |   |                                                                              |
| `same as(y)`                         | Strict identity (`===`). ([Twig][11])                                                                                                        | `$x === $y`                                                                                                                                                             |   |              |   |                              |   |                                                                              |

---

## Migration notes

* **Tests become explicit boolean expressions** in Blade/PHP. Twig’s `is …` reads fluently; in Blade, you typically implement the condition directly (or encapsulate as a small helper if repeated).
* **Be careful with `empty` parity**: Twig’s `empty` test explicitly includes `false` and `null` as “empty,” which may or may not match how you currently use `empty()` / `blank()` in Laravel. ([Twig][6])
* **Prefer moving non-trivial logic out of views**: if Twig templates currently lean on many tests/filters, consider pushing normalization into view models, presenters, or dedicated helpers so Blade stays declarative.

[1]: https://twig.symfony.com/doc/3.x/?utm_source=chatgpt.com "Documentation - Twig - The flexible, fast, and secure PHP ..."
[2]: https://twig.symfony.com/doc/3.x/tests/index.html "Tests - Documentation - Twig PHP"
[3]: https://twig.symfony.com/doc/3.x/tests/constant.html "constant - Tests - Documentation - Twig PHP"
[4]: https://twig.symfony.com/doc/3.x/tests/defined.html?utm_source=chatgpt.com "defined - Tests - Documentation - Twig PHP - Symfony"
[5]: https://twig.symfony.com/doc/3.x/tests/divisibleby.html "divisible by - Tests - Documentation - Twig PHP"
[6]: https://twig.symfony.com/doc/3.x/tests/empty.html "empty - Tests - Documentation - Twig PHP"
[7]: https://twig.symfony.com/doc/3.x/tests/even.html "even - Tests - Documentation - Twig PHP"
[8]: https://twig.symfony.com/doc/3.x/tests/iterable.html "iterable - Tests - Documentation - Twig PHP"
[9]: https://twig.symfony.com/doc/3.x/tests/null.html "null - Tests - Documentation - Twig PHP"
[10]: https://twig.symfony.com/doc/3.x/tests/odd.html "odd - Tests - Documentation - Twig PHP"
[11]: https://twig.symfony.com/doc/3.x/tests/sameas.html "same as - Tests - Documentation - Twig PHP"
