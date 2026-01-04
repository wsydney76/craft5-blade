# Blade

Enables Laravel Blade templates in Craft CMS as a modern alternative to Twig.

README is a work in progress and partially AI-generated.

See [Architecture Overview](./ARCHITECTURE.md) for implementation details.

## Motivation

A client is considering porting a Laravel application to Craft (multi-site, drafts, etc.) that has a large number of Blade templates. This plugin is intended to simplify the evaluation and enable a step-by-step approach.

It is not intended to be a comprehensive or permanent solution, but merely to support this specific project.

Functionality will only be fixed or improved as needed.

## Requirements

This plugin requires Craft CMS 5.8.0 or later, and PHP 8.2 or later.

Internally, it uses the Laravel Illuminate packages (including Blade) version 10.x, matching the version Craft CMS 5.x uses for Laravel collections.

## Installation

Add this to the `composer.json` file in your project root to require the plugin:

```json
{
  "require": {
    "wsydney76/craft5-blade": "dev-main"
  },
  "minimum-stability": "dev",
  "prefer-stable": true,
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/wsydney76/craft5-blade"
    }
  ]
}
```

Then run `composer update` to install the plugin.

Install the plugin: `ddev craft plugin/install _blade`.

## Configuration

Blade supports both **Control Panel (CP) settings** and a config file.

### Control Panel settings page

Once the plugin is installed, you can configure the runtime in the Craft CP:

- **Settings → Plugins → Blade** (`_blade`)

The CP settings page currently exposes these settings:

- `bladeViewsPath` — Base path where Blade views live (e.g. `@root/resources/views`).
- `bladeCachePath` — Directory where compiled Blade templates are written (must be writable).
- `bladeRoutePrefixes` — Comma-separated route prefixes for the direct URL rendering route.
- `bladeComponentPaths` — Anonymous component directories, optionally namespaced by prefix (e.g. `ui` → `<x-ui-*>`).

### Config file (`config/_blade.php`)

If you want to customize Blade settings via code, create a config file at `config/_blade.php`.

If a setting is defined in `config/_blade.php`, it **overrides** the CP value.
Those overridden fields will show a warning in the CP and cannot be edited there.

```php
<?php

use craft\helpers\App;

return [
    'bladeViewsPath' => App::env('BLADE_VIEWS_PATH') ?? '@templates/_blade',
    'bladeCachePath' => App::env('BLADE_CACHE_PATH') ?? '@runtime/blade/cache',

    // Anonymous component roots (optional)
    'bladeComponentPaths' => [
        ['path' => '@templates/_shared', 'prefix' => 'shared'],
    ],

    // Route prefix(es) for direct rendering URLs
    // e.g. /pages/articles/list -> view "pages.articles.list"
    'bladeRoutePrefixes' => 'pages,blog',
];
```

Settings:

* `bladeViewsPath` — Path to the Blade views directory. Defaults to `@root/resources/views`.
* `bladeCachePath` — Path to the compiled Blade template cache directory. Defaults to `@runtime/blade/cache`.
* `bladeComponentPaths` — Additional anonymous component paths with (optional) prefixes.
* `bladeRoutePrefixes` — Prefixes for URL routes pointing directly to Blade templates. Defaults to `blade`. Comma-separated values; multiple routes will be registered.

Path values support Craft aliases (e.g. `@root`, `@runtime`).

If the `bladeViewsPath` is changed, you may need to adjust your IDE settings to recognize Blade templates in that directory.

See [Customize](#config-driven-customization) for additional configuration options.

## Features

- **Full Blade syntax support** — Use Laravel Blade features including components, directives, and control structures.
- **Blade components** — Create and use reusable components with props.
- **Custom directives** — Define custom Blade directives for your application.
- **Twig integration** — Call Twig templates from Blade using the `@renderTwig()` directive.
- **Global data sharing** — Access Craft global variables in Blade templates (like `craft`, site name, etc.).
- **Template inheritance** — Use Blade's powerful layout system with `@extends` and `@section`.

## Limitations

- Does not support Laravel-specific helper functions and Blade directives that depend on Laravel features not present in Craft CMS.
- Does not offer equivalent functionality for some advanced Craft Twig features/tags (e.g. `nav`).
- Does not fully support [Template localization](https://craftcms.com/docs/5.x/development/templates.html#template-localization)
- Currently only used with the Entry element type. Other element types may work but are not yet tested.
- The central `BladeBootstrap.php` class is mostly AI-generated and may look like a complete mess for Laravel/Blade experts. But it works for the tested use cases...
- Not yet reviewed in terms of performance/memory usage.
- Support for Craft's Twig functions and filters is experimental.
- Does not support Livewire-like reactive components out of the box.

## Usage

### The View singleton

The main entry point for interacting with Blade is the `wsydney76\blade\View` class. 

Missing methods can be added as needed.

### Basic setup

Create your Blade templates in the `resources/views` directory (or the path configured in `config/_blade.php`).

The template cache is stored in `storage/runtime/blade/cache` (or the path configured in `config/_blade.php`).

### Creating Blade Templates

Create `.blade.php` files in your views directory:

```blade
<x-layout :title="$entry->title">
    <article class="prose prose-lg max-w-none">
        @if ($entry->image)
            <x-image :image="$entry->image->one()" width="1024" height="400" />
        @endif

        <h1 class="text-3xl font-bold">{{ $entry->title }}</h1>

        <x-meta :entry="$entry" />

        @if ($entry->teaser)
            <p class="my-4 text-xl font-bold">{{ $entry->teaser }}</p>
        @endif
        
        @markdown($entry->body)

        <div class="mx-auto mt-8">
            <x-blocks :blocks="$entry->bodyContent->all()" />
        </div>
    </article>
</x-layout>
```

### Components

Create reusable components in `resources/views/components/` (or the paths configured in `config/_blade.php`):

#### Anonymous components (view-only)

Anonymous components are just Blade views in your components folder.

#### Layout Component (`layout.blade.php`)
```blade
@props(['title' => 'My Site'])

<!DOCTYPE html>
<html>
<head>
    ...
    <title>{{ $title }}</title>
    {!! Vite::getInstance()->vite->script('/resources/js/app.js', false) !!}
</head>
<body>
    @renderTwig('_layouts/nav.twig')
    ...
    <main>
        {{ $slot }}
    </main>
    ...
</body>
</html>
```

#### Image Component (`image.blade.php`)
```blade
@props(['image' => null, 'width' => null, 'height' => null])

@if ($image)
    {!! $image->getImg(['width' => $width, 'height' => $height]) !!}
@endif
```

Use the components like this:
```blade
<x-layout title="My Page">
    <x-image :image="$entry->image->one()" width="600" height="400" />
    ... content ...
</x-layout>
```

#### Class-based components

##### 1) Create the component class

Example: `modules/main/components/EntriesList.php`

```php
<?php

namespace modules\main\components;

use craft\elements\Entry;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class EntriesList extends Component
{
    public string $section;
    public ?string $title;
    public int $limit;
    public string $orderBy = 'postDate desc';
    public ?Collection $entries = null;

    public function __construct(
        string $section = '*',
        ?string $title = null,
        ?int $limit = 5,
        ?string $orderBy = 'postDate desc')
    {
        $this->section = $section;
        $this->title = $title;
        $this->limit = $limit;
        $this->orderBy = $orderBy;
        $this->entries = Entry::find()
            ->section($section)
            ->limit($limit)
            ->orderBy($orderBy)
            ->collect();
    }

    public function render(): View
    {
        return view('components.entries-list');
    }
}
```

##### 2) Create the component view

Example: `resources/views/components/entries-list.php`

When using class-based components, prefer using the component’s **public props** directly.
(Depending on your Illuminate/View version and how the engine is bootstrapped, `$component` may not be available.)

```blade
@if($entries->count())
    @if($title)
        <h3>{{ $title }}</h3>
    @endif

    <ul>
        @foreach($entries as $entry)
            <li>
                <a href="{{ $entry->url }}" class="text-blue-600 hover:underline">
                    {{ $entry->title }}</a>
                <span class="text-sm text-gray-600">{{ $entry->postDate }}</span>
            </li>
        @endforeach
    </ul>
@endif
```

##### 3) Register the component

Register class-based components in your module/plugin bootstrap:

```php
use modules\main\components\EntriesList;
use wsydney76\blade\View;

View::component('entries-list', EntriesList::class);
```

##### 4) Use it in Blade

```blade
<x-entries-list section="article" :title="t('Latest via class component')" />
```

#### Dynamic Components

Supports dynamic components:

```blade
@foreach ($entry->myMatrixField->all() as $block)
    <x-dynamic-component
        :component="'blocks.' . $block->type->handle"
        :block="$block" />
@endforeach
```

### Rendering from PHP

Render Blade templates from plugins or controllers:

```php
use wsydney76\blade\View;

View::renderTemplate('mytemplate', [
    'entries' => $entries
])
```
Accepts an array of views, the first existing one will be used:

```php
View::renderTemplate(['custom.template', 'fallback.template'], [
    'data' => $data
])
```

You can also use a more familiar syntax:

```php
return view('greeting')
    ->with('name', 'Victoria')
    ->with('occupation', 'Astronaut');

return View::first(['custom.admin', 'admin'], $data);
```

### Using Twig from Blade

Call Twig templates using the `@renderTwig()` directive:

```blade
@renderTwig('_layouts/nav.twig', [...someData...])
```

### Using Blade from Twig

Call Blade templates using the `renderBlade()` function:

```twig
{{ renderBlade('component.blocks.text', { text: 'Craft', class: 'text-xl' }) }}
```

This can be used to embed Blade components in Twig layouts, so that you can gradually migrate templates.

```twig
{% extends "_layouts/main.twig" %}

{% block proseContent %}
    {{ renderBlade('film.filmdetails', {entry}) }}
{% endblock %}
```

### Routing for Craft entries

In order to use Blade templates for Craft entries, set the template in the section settings

#### Using a custom controller action

* `action:main/blog/show` by route (controller action)

```php
use wsydney76\blade\View;
...
public function actionShow(): string
    {
        $entry = Craft::$app->urlManager->getMatchedElement();
        if (!$entry) {
            throw new NotFoundHttpException('Page not found');
        }

        $prevNextCriteria = [
            'section' => $entry->section->handle,
        ];

        return View::renderTemplate('article', [
            'entry' => $entry,
            'prev' => $entry->getPrev($prevNextCriteria),
            'next' => $entry->getNext($prevNextCriteria)
        ]);
    }
```

The current element can be accessed via `Craft::$app->urlManager->getMatchedElement()`.

Craft will automatically set the correct content type header `text/html` for the response.

#### Directly to a Blade template

* `blade:blog.show` (by prefix) 
* `blog/show.blade.php` (by file path/extension, relative to bladeViewsPath setting).

The current element is available in Blade automatically:

- It’s injected into the view context based on the element’s short class name (lowercased), e.g. `Entry` → `$entry`, `Product` → `$product`.

### Direct URL rendering route (`/{prefix}/{view}`)

The plugin registers a **site route** that can render a Blade view directly from a URL.

This is mainly used for routes that do not correspond to Craft elements, e.g. static pages or special endpoints.

- Default prefix: `blade`
- Config key: `bladeRoutePrefixes` (plugin settings / `config/_blade.php`). Comma-separated values; multiple routes will be registered. 

Examples (default prefix):

- `/blade/articles` renders Blade view `blade.articles`
- `/blade/articles/list/bydate` renders Blade view `blade.articles.list.bydate`

Notes:

- The `{view}` portion is treated as a slash-delimited path and is normalized to a dotted view name.
- The endpoint is accessible anonymously by default.
- You’re responsible for implementing appropriate security measures (e.g. access control, input validation, and sanitization of user-provided data).

To customize the prefix, add this to `config/_blade.php`:

```php
return [
    'bladeRoutePrefixes' => 'views,pages'
];
```

### Using custom controller actions

Custom controller actions can be set up using the usual Craft mechanisms and finally render Blade templates using `View::renderTemplate()`.

### Template Localization

By default, Blade has no awareness of Craft's [template localization](https://craftcms.com/docs/5.x/development/templates.html#template-localization).

As a workaround, pass an array of possible localized templates to Blade where needed:

PHP:

```php
$currentSite = Craft::$app->getSites()->getCurrentSite();
return View::renderTemplate(["{$currentSite->handle}.article.index", 'article.index'], [...]);
```

Blade:

```blade
@includeFirst(["{$currentSite->handle}.meta", 'meta'], ['entry' => $entry])
```

Components are not supported.

Some helper functions are available to simplify this, but not fully tested yet:

PHP:

```php
View::renderLocalized('article.show', [...]);
```

Blade:

```blade
@includeLocalized('meta', ['entry' => $entry])  
```

### Handling pagination

Experimental.

#### PHP: Using View::paginate()

Handle pagination in the controller using the `View::paginate()` helper method:

```php
use wsydney76\blade\View;
...

public function actionIndex()
{
    return View::renderTemplate(
            'posts.index',
            [
                'entry' => Craft::$app->urlManager->getMatchedElement(),
                ...View::paginate(Entry::find()->section('post')->limit(10), 'posts', 'pageInfo')
            ],
        );
}
```

The `View::paginate()` method accepts:
- `$query` - The element query, optionally with limit set
- `$resultsKey` - The key name for results (default: 'elements')
- `$pageInfoKey` - The key name for page info (default: 'pageInfo')
- `$config` - Optional configuration array (pageSize, currentPage, etc.).

An array with the keys specified in `$resultsKey` and `$pageInfoKey` is returned, where
* `$resultsKey` contains a collection with the paginated results
* `$pageInfoKey` contains an instance of `craft\web\twig\variables\Paginate`. See [docs](https://craftcms.com/docs/5.x/reference/twig/tags.html#the-pageinfo-variable) for details.

To determine the current page, `Craft::$app->request->getPageNum()` is used, respecting the `pageTrigger` general setting.

To determine the page size, either the limit set on the query or the `pageSize` config is used. If not set, defaults to 100.

#### Blade: Using @paginate() directive

Alternatively, handle pagination directly in the Blade template using the `@paginate()` directive:

```blade
@paginate(Entry::find()->section('post')->limit(4), 'posts', 'pageInfo')
```

#### Using pagination in templates

Both methods provide `$posts` with the page results and `$pageInfo` with pagination information:

```blade
<ul>
    @foreach($posts as $post)
        <li>{{ $post->title }}</li>
    @endforeach
</ul>

<p>
    Showing page {{ $pageInfo->currentPage }} of {{ $pageInfo->totalPages }}.

    @if($pageInfo->currentPage > 1)
        <a href="{{ $pageInfo->getPrevUrl() }}">Previous page</a>
    @endif

    @if($pageInfo->currentPage < $pageInfo->totalPages)
        <a href="{{ $pageInfo->getNextUrl() }}">Next page</a>
    @endif
</p>
```

#### Template fragment caching (`@cache ... @endcache`)

Experimental.

The `@cache` directive pair mirrors Craft’s Twig `{% cache %}` tag behavior.

Basic usage (no options):

```blade
@cache
    ... the content to cache ...
@endcache
```

With options (all keys optional):

```blade
@cache([
    'key' => 'thekey',
    'global' => true,
    'duration' => '1 hour',
    'expiration' => 1735689600,
])
    Hallo
@endcache
```

Conditional caching (Craft Twig `{% cache if ... %}` / `{% cache unless ... %}` equivalents):

```blade
@cache(['if' => craft()->app->request->isMobileBrowser()])
    This is only cached for mobile browsers.
@endcache

@cache(['unless' => $currentUser])
    This is cached unless a user is logged in.
@endcache
```

Options:

- `key` (string): Cache key override. If omitted, a deterministic key is generated.
- `global` (bool): Whether the cache is global. Default: `false`.
- `duration` (?string): Cache duration (e.g. `'1 hour'`). Default: `null`.
- `expiration` (mixed): Explicit expiration value (timestamp/DateTime/etc.). Default: `null`.
- `if` (mixed): Only use the cache when this is truthy.
- `unless` (mixed): Only use the cache when this is falsey.

Note: Uses Craft's TemplateCaches service under the hood, so (in theory) should behave the same, including cache invalidation.

Cache fragments created via Twig and via Blade are interoperable: if both use the same cache key and are 'global', they refer to the same underlying Craft template cache entry and can be reused interchangeably.

### Accessing Craft Global Variables

All [Craft global variables](https://craftcms.com/docs/5.x/reference/twig/global-variables.html#craft) (except `_globals`) are available in Blade templates:

```blade
<p>App Name: {{ $systemName }}</p>
<p>Site URL: {{ $siteUrl }}</p>
<p>User name: {{ $currentUser->name }}</p>
<p>Craft variable: {{ $craft->app->language }}</p>

@php($entries = $craft->entries()->section('*')->all())
```

## Helper Functions and Filters

> For a mapping of Twig’s built-in functions and filters to Blade helper functions, see the [TWIG_MAPPINGS.md](ai-generated-docs/TWIG_MAPPINGS.md) file.

Experimental.

As a first step towards supporting Craft's Twig functions and filters in Blade templates, the Craft Twig extension was fed into an AI model, and the functions and filters were converted to standalone PHP functions in `BladeHelpers.php` and `BladeFilters.php`, along with some docs.

These results are published here unedited and untested for evaluation; no guarantees are made regarding completeness or correctness.

Blade helper functions are automatically available in your templates and include:

- **Craft CMS functions** - URL helpers, config helpers, element queries, etc.
- **Twig filters as functions** - Most Craft CMS Twig filters are available as PHP functions for use in Blade (see `BLADE_FILTERS_MAPPING.md` for a complete list)
- **HTML helpers** - Common HTML output functions
- **Translation helper** - `__()` function for translation

See [BLADE_FUNCTIONS_MAPPING.md](ai-generated-docs/BLADE_FUNCTIONS_MAPPING.md) and [HELPER_FILTERS_MAPPING.md](./ai-generated-docs/BLADE_FILTERS_MAPPING.md) for mapping to Craft's core functionality.

See [BLADE_FUNCTIONS_QUICK_REFERENCE.md](ai-generated-docs/BLADE_FUNCTIONS_QUICK_REFERENCE.md) and [BLADE_FILTERS_QUICK_REFERENCE](./ai-generated-docs/BLADE_FILTERS_QUICK_REFERENCE.md) for mapping to Craft's core functionality.

In the current state of this PoC, no further work is planned except for fixing concrete issues as they arise.

Note that some functions and filters must not be escaped in Blade templates to work correctly, e.g. HTML output functions like `csrfInput()`. Use `{!! ... !!}` instead of `{{ ... }}` for these.

Possible next steps:

* Testing...
* Drop functions that have equivalents in Laravel Blade (e.g. dump, dd).
* Drop functions that have equivalents in Laravel Helper classes? (e.g. Arr::xxx, Str::xxx).
* Drop functions that map directly to PHP native functions (e.g. array handling).
* Drop functions that map directly to Craft helper methods? (e.g. siteUrl() => UrlHelper::siteUrl()).
* Drop functions that map directly to Craft services? (e.g. entryType() ⇒ Craft::$app->getEntries()->getEntryTypeByHandle()).
* Implement as directives instead of functions in order to avoid escaping issues? (e.g. `@csrfInput` instead of `{!! csrfInput() !!}`).
* Drop functions that will most likely never be used in a lifetime (e.g. `gql()`).
* Implement Laravel style helper functions for common services? (e.g. `request()` vs. `Craft::$app->getRequest()`).
* Check Craft's Twig tags and see if some can be implemented as Blade directives (e.g. `requireAdmin`).

### Predefined Directives

The following Blade directives are predefined:

- `@markdown($text, $flavor = 'original', $purifierConfig = null)` - Render purified Markdown content to HTML
- `@paginate($query, $resultsKey = 'elements', $pageInfoKey = 'pageInfo')` - Handle pagination for an element query (experimental)
- `@renderTwig($template, $data = [])` - Render a Twig template from Blade
- `@includeLocalized($template, $data = [])` - Include a localized template (experimental)
- `@requireAdmin` - Require admin access for the current user (throws 403 otherwise)
- `@requirePermission($permission)` - Require a specific permission for the current user (throws 403 otherwise)
- `@requireLogin` - Require the user to be logged in (throws 403 otherwise)
- `@requireGuest` - Require the user to be logged out (throws 403 otherwise)
- `@redirect($url, $statusCode=302)` - Redirects to a given URL (throws a redirect response).
- `@header($headerLine)` - Sets an HTTP response header, matching Craft’s Twig `{% header %}` tag compiler behavior
- `@cache($options = []) ... @endcache` - Template fragment caching (Craft’s Twig `{% cache %}` equivalent)

## Customize

Blade can be customized from your module or plugin by registering custom directives, components, stringables, shared data, view composers, etc.

Customizations can be defined 

* in your controller (preferred for best granular control),
* in the `init()` method of your module/plugin bootstrap class (place in `Craft::$app->onInit` callback to ensure Craft is fully initialized),
* or in the `config/_blade.php` config file (see [Config-driven Customization](#config-driven-customization) below).

### Custom Directives

Define custom Blade directives in your plugin or module:

```php
View::directive('datetime', function($expression) {
    return "<?php echo ($expression)->format('Y-m-d H:i'); ?>";
})
```

Usage in Blade templates:

```blade
<p>Published at: @datetime($entry->postDate)</p>
```

### Shared Data

Share global data across all Blade templates:

```php
View::share('settings', Entry::find()->section('settings')->one());
```

Then access it in any Blade template:

```blade
<footer class="mt-12 border-t border-b-gray-500 pt-4">
    &copy; {{ $settings->copyright }} {{ $now->format('Y') }}
</footer>
```

This mimics Craft's `preloadSingles` feature for Twig templates. Kind of.

### Custom If Statements

Define custom Blade If statements in your plugin or module:

```php
View::if('dev', function (): bool {
    return Craft::$app->getConfig()->getGeneral()->devMode;
});
```

Usage in Blade templates:

```blade
@dev  
    <p>Running in dev mode</p>
@else   
    <p>Running in production mode</p>
@endadmin


@unlessdev
   <p>Running in production mode</p>
@enddev
```

### Custom Echo Handlers

Register custom stringable handlers to automatically format objects that don't implement a `__toString` method:

```php
View::stringable(\DateTime::class, function($dateTime) {
    return $dateTime->format('Y-m-d H:i');
});
```

Usage in Blade templates:

```blade
<p>Posted: {{ $entry->postDate }}</p>
<!-- Outputs: Posted: 2025-12-28 14:30 -->
```

Multiple stringables can be registered for different classes:

```php
// Format DateTime objects
View::stringable(\DateTime::class, function($dateTime) {
    return $dateTime->format('Y-m-d H:i');
});

// Format Money objects (example)
View::stringable(Money\Money::class, function($money) {
    if ($money === null) {
        return null;
    }
    return \craft\helpers\MoneyHelper::toString($money);
});
```

Note that you can't pass additional parameters to the stringable handler. If you need more control, consider using a custom Blade directive or helper function instead.

### View Composers

Laravel-style Blade view composers are supported.

This lets you attach data to views globally or per-view, without having to pass everything from every controller.

Register composers from your module/plugin:

```php
use wsydney76\blade\View;

View::composer('article.show', function ($view) {
    $view->with('composerMessage', 'Injected by a view composer');
});

// Wildcards are supported by the underlying Illuminate view factory:
View::composer('*', function ($view) {
    $view->with('composerMessage', 'Injected by a view composer');
});
```

Then use the injected variables in your Blade template:

```blade
{{ $composerMessage }}
```

### Config-driven Customization

Experimental, AI generated.

For convenience, you can also define customizations in the `config/_blade.php` config file.

```php
return [
    'bladeShared' => [
        'copyright' => '© ' . date('Y'),
        'settings' => Entry::find()->section('settings')->one(),
    ],

    'bladeDirectives' => [
        'relativeTime' => function($expression) {
            return "<?php echo Craft::\$app->getFormatter()->asRelativeTime($expression); ?>";
        },
    ],

    'bladeStringables' => [
        \DateTime::class => function($dateTime) {
            return $dateTime->format('Y-m-d H:i');
        },
    ],

    'bladeIfs' => [
        'itsFriday' => function (): bool {
            return date('N') === '5';
        },
    ],

    'bladeComponents' => [
        'alert' => Alert::class,
    ],

    'bladeViewComposers' => [
        'config' => function ($view) {
            $view->with('entriesCount', Entry::find()->section('*')->count());
        },
    ],
];
```

Note: while this seems convenient, you lose control over when exactly customizations are registered. So this may have a negative impact on performance, e.g. when unnecessary queries are executed.

### Custom View Helper Functions and Filters

Helpers are regular PHP functions. Define them in regular PHP files that are required by your module/plugin. Note that loading via composer autoloading may not work because Craft is not initialized at that point.

### Common Blade Settings

If multiple controllers are used, extend from a base controller to set common Blade settings:

```php
use wsydney76\blade\View;
...
public function beforeAction($action): bool
    {
      
        // Share global settings entry
        View::share('settings', Entry::find()->section('settings')->one());

        // Register stringable for DateTime objects
        View::stringable(\DateTime::class, function($dateTime) {
            return $dateTime->format('Y-m-d H:i');
        });

        // Datetime directive
        View::directive('datetime', function($expression) {
            return "<?php echo ($expression)->format('Y-m-d H:i'); ?>";
        });
        return parent::beforeAction($action);
    }
```

## Laravel Helpers

The plugin installs the `Illuminate/Support` package as a dependency, which provides Laravel's helper classes like `Arr`, `Str`, etc.

You can use these classes in your Blade templates and PHP code as needed, which is especially useful when porting existing Laravel applications.

Note that some functionality may not work as expected outside a full Laravel application context. This may especially apply to `facades`, because, well, there is nothing behind the facade.

```blade
@use('Illuminate\Support\Arr')
@use('Illuminate\Support\Str')
@use('Illuminate\Support\Number')
@use('Illuminate\Support\Pluralizer')

@dump(Arr::add(['name' => 'Desk'], 'price', 100))
@dump(Arr::crossJoin([1, 2], ['a', 'b']))

{{ Str::repeat('abc', 3) }}
{{ Str::replaceFirst('_', ':', 'abc_def_ghi') }}

{{-- Fluent strings (Str::of) allow chaining operations in a readable way --}}
{{ Str::of('  hello ? from ?  ')->trim()->replaceArray('?', ['world', 'blade'])->headline()->append('!') }}

{{ Number::ordinal(21) }}
{{ Number::clamp(105, min: 10, max: 100) }}

{{ Pluralizer::plural('item') }}  = items 
{{ Pluralizer::plural('person', 3) }} = people
{{ Pluralizer::singular('geese') }} = goose

```

## Clearing Cache

Remove cached Blade templates via console command:

```bash
php craft clear-caches/blade
```

Or via Control Panel: Utilities → Caches → Blade Template Cache.

The template cache has to be cleared when Blade custom directives are updated.

## Reactive components

Livewire-like reactive components are not supported, as Livewire is deeply bound to core Laravel. 

Consider porting existing components to Twig using the Sprig plugin (or similar).

Otherwise, you can integrate with Alpine.js (which is used by Livewire behind the scenes) to come somewhat close and keep most of your controller logic and templates.

See [REACTIVECOMPONENTS.md](./REACTIVECOMPONENTS.md) for an example implementation of a reactive search component using Alpine.js.

## Plugin Integration

Craft plugins that work with Twig should also work with Blade

* if they expose functionality via the Craft variable (e.g. `craft.thePlugin.doSomething`)
* if they expose functionality via plain PHP classes/services

Plugins that expose functionality via Twig extensions (functions, filters, tags) will not work out of the box.

### Examples

#### Vite

```blade
{!! $craft->vite->script('/resources/js/app.js', false) !!}
```

#### Imagerx

Example component usage:

```blade
<x-image class="my-8" :image="$image" :transform="['width' => 768, 'ratio' => 25/9]" />
```

The component code (e.g. in `resources/views/components/image.blade.php`):

```blade
@props([
    'image' => null,
    'transform' => ['width' => 800, 'ratio' => 16 / 9],
])
@if ($image)
    <img
        {{ $attributes }}
        src="{{ $craft->imagerx->transformImage($image, $transform) }}"
        alt="{{ $image->alt ?? $image->title }}"
    />
@endif   
```

Or register the component globally in your module/plugin bootstrap:

```php
View::share('imagerx', Craft::$app->plugins->getPlugin('imager-x')->imager);
```

Then use it in Blade templates:

```blade     
src="{{ $imagerx->transformImage($image, $transform) }}"  
```
    
#### Blitz

Needs confirmation, but guessing that Blitz does not care about the template engine used.

```blade     
@php($craft->blitz->options(['cachingEnabled' => false])) 
```

## Using Eloquent ORM

If a Laravel app is being ported to Craft CMS, you may want to keep using Eloquent ORM for accessing custom tables you don't want to migrate to Craft elements.

See [ELOQUENT.md](./ELOQUENT.md) for a starting point on how to set up Eloquent in Craft CMS.

You will finally want to migrate to Craft's `ActiveRecord` models, but this may help to get started quickly.

## IDE Support

Make sure plugins supporting Laravel/Blade are installed and enabled. PhpStorm >= 2025.3 has some Laravel support built-in.

### PhpStorm

* Install the Laravel Idea plugin
* PHP → Blade: Add custom directives for better code completion
* Editor → General → Appearance: Check "Always enable Blade template highlighting"
* Languages & Frameworks → Laravel Idea → Views: Check "Default Views Path"
* Languages & Frameworks → Laravel Idea → Languages → Blade: Check "Blade component views directory"
* Editor → Live Templates: You may want to add custom Blade snippets here for convenience.

Examples:

* Abbreviation: `bfor` (or anything unique you like)
* Applicable in: HTML (not PHP!)
* Edit Variables: ARRAY = `"$entries"`, VARIABLE = `"$entry"`, STUFF = `""`
```
@foreach($ARRAY$ as $VARIABLE$)
    $STUFF$$END$
@endforeach
```

* Abbreviation: `bnl2br` (or anything unique you like)
* Applicable in: HTML (not PHP!)
* Edit Variables: VARIABLE = `"$variable"`
```
{!! nl2br(e($VARIABLE$)) !!}
```

### Other IDEs

No experience with other IDEs, but guessing that similar settings should be available.

### Code Completion

This should pick up custom fields for Craft elements, make sure `storage/runtime/compiled-classes` is indexed by your IDE (mark directory as 'not excluded', if necessary).

Type hints can be added in Blade templates using `@php` blocks.

```blade
@php
    /** @var \craft\elements\Entry $entry */
@endphp

<h1>{{ $entry->title }}</h1>

<p> {{ $entry->myCustomField }}</p>
```

If you want to define type hints globally for common variables like `$entry`, place a PHP file anywhere where the IDE indexes it:

```php
<?php

/** @var \craft\elements\Entry $entry */
global $entry;

/** @var \craft\elements\Asset $asset */
global $asset;

/** @var \craft\elements\Asset $image */
global $image;
```

### Using prettier for Blade/Tailwind formatting

Example setup, adjust to your needs.

Update your `package.json` to include `prettier-plugin-blade` and `prettier-plugin-tailwindcss`:

```json
{
  "scripts": {
    "prettier-views": "npx prettier --write \"resources/views\" --parser blade"
  },
  "devDependencies": {
    "@prettier/plugin-php": "^0.24.0",
    "prettier": "^3.6.2",
    "prettier-plugin-blade": "^2.1.21",
    "prettier-plugin-tailwindcss": "^0.6.14"
  }
}
```

Then run `npm install` to install the packages.

Create a `.prettierrc` config file in your project root:

```json
{
  "plugins": [
    "@prettier/plugin-php",
    "prettier-plugin-tailwindcss",
    "prettier-plugin-blade"
  ],
  "singleQuote": true,
  "tabWidth": 4,
  "printWidth": 100,
  "semi": true,
  "trailingComma": "es5",
  "tailwindStylesheet": "./resources/css/app.css"
}
```

Run `npm run prettier-views` to format all Blade templates in the `resources/view` directory.

PHPStorm settings (may differ for different versions):

* Languages & Frameworks → JavaScript → Prettier:
  * Include blade suffix in  `Run for files`: `**/*.{js,ts,jsx,tsx,cjs,cts,mjs,mts,json,vue,astro,blade.php,php}
  * Check `Automatic prettier configuration`, `Run on save`, `Run on paste`, `Prefer prettier configuration to IDE code style`.
* Tools → Actions on Save: Check `Run prettier`, disable `Reformat code`.
* Languages & Frameworks → JavaScript → Runtime: Check that Node runtime is set correctly.

