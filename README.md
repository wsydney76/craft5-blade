# Blade

Enables Laravel Blade templates for Craft CMS, providing a modern templating engine alternative to Twig.

Readme work in progress (and partially AI generated, so...).

## Requirements

This plugin requires Craft CMS 5.8.0 or later, and PHP 8.2 or later.

## Installation

Add to `composer.json` file in your project root to require this plugin:

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

Run `ddev craft plugin/install _blade`.

## Features

- **Full Blade syntax support** - Use all Laravel Blade features including components, directives, and control structures
- **Blade components** - Create and use reusable components with props
- **Custom directives** - Define custom Blade directives for your application
- **Twig integration** - Call Twig templates from Blade using the `@renderTwig()` directive
- **Global data sharing** - Access Craft global variables in Blade templates (like `craft`, site name, etc.)
- **Template inheritance** - Use Blade's powerful layout system with `@extends` and `@section`


## Limitations

- Does not support Laravel-specific helper functions and blade directives that depend on Laravel features not present in Craft CMS.
- Does not offer equivalent functionality for advanced Craft Twig features, e.g.`cache` or `nav` twig tags.
- Does not fully support [Template localization](https://craftcms.com/docs/5.x/development/templates.html#template-localization)
- For now, only used for entry element type. Should work with other element types but not yet tested.
- IDE support for Blade templates in Craft projects may be limited compared to Twig, e.g. there is no code completion for custom fields.
- The central BladeBootstrap.php class is mostly AI generated and may look like a complete mess for Laravel/Blade experts. But it works for the tested use cases...
- Not yet reviewed in terms of performance/memory usage.
- Support for Craft's Twig functions and filters is experimental.
- Does not support class-based components.
- Does not support Livewire-like reactive components out of the box.
- Does not support view composers.

## Helper Functions and Filters

Experimental.

As a first step towards supporting Craft's Twig functions and filters in Blade templates, the Craft Twig extension was thrown into AI, and the functions and filters were converted to standalone PHP functions in `BladeHelpers.php` and `BladeFilters.php`, along with some docs.

Publishing these results here unedited and untested for further evaluation, but no guarantees regarding completeness or correctness.

Blade helper functions are automatically available in your templates and include:

- **Craft CMS functions** - URL helpers, config helpers, element queries, etc.
- **Twig filters as functions** - Most Craft CMS Twig filters are available as PHP functions for use in Blade (see `BLADE_FILTERS_MAPPING.md` for a complete list)
- **HTML helpers** - Common HTML output functions
- **Translation helper** - `__()` function for translation

See  [BLADE_FUNCTIONS_MAPPING.md](ai-generated-docs/BLADE_FUNCTIONS_MAPPING.md) and [HELPER_FILTERS_MAPPING.md](./ai-generated-docs/BLADE_FILTERS_MAPPING.md) for mapping to Craft's core funtionality.

See  [BLADE_FUNCTIONS_QUICK_REFERENCE.md](ai-generated-docs/BLADE_FUNCTIONS_QUICK_REFERENCE.md) and [BLADE_FILTERS_QUICK_REFERENCE](./ai-generated-docs/BLADE_FILTERS_QUICK_REFERENCE.md) for mapping to Craft's core funtionality.

In the current state of this PoC, no further work will be done, accept fixing concrete issues as they arise.

Note that some functions and filters must not be escaped in Blade templates to work correctly, e.g. HTML output functions like `csrfInput()`. Use `{!! ... !!}` instead of `{{ ... }}` for these.

Possible next steps:

* Testing...
* Drop functions that have equivalents in Laravel Blade (e.g. dump, dd).
* Drop functions that map directly to PHP native functions (e.g. array handling).
* Drop functions that map directly to Craft helper methods? (e.g. siteUrl() => UrlHelper::siteUrl()).
* Drop functions that map directly to Craft services? (e.g. entryType() => Craft::$app->getEntries()->getEntryTypeByHandle()).
* Implement as directives instead of functions in order to avoid escaping issues? (e.g. `@csrfInput` instead of `{!! csrfInput() !!}`).
* Drop functions that will most likely never be used in a lifetime (e.g. `gql()`).
* Implement Laravel style helper functions for common services? (e.g. `request()` vs. `Craft::$app->getRequest()`).
* Check Craft's Twig tags and see if some can be implemented as Blade directives (e.g. `requireAdmin`).

## Usage

### Basic Setup

Create your Blade templates in the `resources/views` directory (or the path configured in `config/_blade.php`).

Template cache is stored in `storage/runtime/blade/cache` (or the path configured in `config/_blade.php`).

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

### Predefined Directives

The following Blade directives are predefined:
- `@markdown($text, $flavor = 'original', $purifierConfig = null)` - Render purified Markdown content to HTML
- `@paginate($query, $resultsKey = 'elements', $pageInfoKey = 'pageInfo')` - Handle pagination for an element query (experimental)
- `@renderTwig($template, $data = [])` - Render a Twig template from Blade
- `@includeLocalized($template, $data = [])` - Include a localized template (experimental)

See below for details.

### Components

Create reusable components in `resources/views/components/`:

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

Use components with:
```blade
<x-layout title="My Page">
    <x-image :image="$entry->image->one()" width="600" height="400" />
    ... content ...
</x-layout>
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
use wsydney76\blade\Blade;

Blade::render('mytemplate', [
    'entries' => $entries
])
```
Accepts an array of views, the first existing one will be used:

```php
Blade::render(['custom.template', 'fallback.template'], [
    'data' => $data
])
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

### Routing for Craft entries

In order to use Blade templates for Craft entries, set the template in the section settings

* to a custom controller action that renders a Blade template: `action:main/blog/show`

```php
use wsydney76\blade\Blade;
...
public function actionShow()
    {
        $entry = Craft::$app->urlManager->getMatchedElement();
        if (!$entry) {
            throw new NotFoundHttpException('Page not found');
        }

        $prevNextCriteria = [
            'section' => $entry->section->handle,
        ];

        return Blade::render('article', [
            'entry' => $entry,
            'prev' => $entry->getPrev($prevNextCriteria),
            'next' => $entry->getNext($prevNextCriteria)
        ]);
    }
```

* or to a Blade template directly: `blade:blog.show` (by prefix) or `blog/show.blade.php` (by file path/extension, relative to bladeViewsPath setting).

In both cases, the current element can be accessed via `Craft::$app->urlManager->getMatchedElement()`.

### Template Localization

Blade by default can't know anything about Craft's [template localization](https://craftcms.com/docs/5.x/development/templates.html#template-localization).

As a workaround, pass an array of possible localized template to Blade where needed:

PHP:

```php
$currentSite = Craft::$app->getSites()->getCurrentSite();
return Blade::render(["{$currentSite->handle}.article.index", 'article.index'], [...]);
```

Blade:

```blade
@includeFirst(["{$currentSite->handle}.meta", 'meta'], ['entry' => $entry])
```

Components are not supported.

Some helper functions are available to simplify this, but not fully tested yet:

PHP:

```php
Blade::renderLocalized('article.show', [...]);
```

Blade:

```blade
@includeLocalized('meta', ['entry' => $entry])  
```

### Handling pagination

Experimental.

#### PHP: Using Blade::paginate()

Handle pagination in the controller using the `Blade::paginate()` helper method:

```php
use wsydney76\blade\Blade;
...

public function actionIndex()
{
    return Blade::render(
            'posts.index',
            [
                'entry' => Craft::$app->urlManager->getMatchedElement(),
                ...Blade::paginate(Entry::find()->section('post')->limit(10), 'posts', 'pageInfo')
            ],
        );
}
```

The `Blade::paginate()` method accepts:
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

### Accessing Craft Global Variables

All [Craft global variables](https://craftcms.com/docs/5.x/reference/twig/global-variables.html#craft) (except `_globals`) are available in Blade templates:

```blade
<p>App Name: {{ $systemName }}</p>
<p>Site URL: {{ $siteUrl }}</p>
<p>User name: {{ $currentUser->name }}</p>
<p>Craft variable: {{ $craft->app->language }}</p>

@php($entries = $craft->entries()->section('*')->all())
```

## Custom Directives

Define custom Blade directives in your plugin or module:

```php
Blade::directive('datetime', function($expression) {
    return "<?php echo ($expression)->format('Y-m-d H:i'); ?>";
})
```

Usage in Blade templates:

```blade
<p>Published at: @datetime($entry->postDate)</p>
```

## Shared Data

Share global data across all Blade templates:

```php
Blade::share('settings', Entry::find()->section('settings')->one());
```

Then access it in any Blade template:

```blade
<footer class="mt-12 border-t border-b-gray-500 pt-4">
    &copy; {{ $settings->copyright }} {{ $now->format('Y') }}
</footer>
```

This mimics Craft's `preloadSingles` feature for Twig templates. Kind of.

## Custom If Statements

Define custom Blade If statements in your plugin or module:

```php
Blade::if('dev', function (): bool {
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

## Custom Echo Handlers

Register custom stringable handlers to automatically format objects that don't implement a `__toString` method:

```php
Blade::stringable(\DateTime::class, function($dateTime) {
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
Blade::stringable(\DateTime::class, function($dateTime) {
    return $dateTime->format('Y-m-d H:i');
});

// Format Money objects (example)
Blade::stringable(Money\Money::class, function($money) {
    if ($money === null) {
        return null;
    }
    return \craft\helpers\MoneyHelper::toString($money);
});
```

Note that you can't pass additional parameters to the stringable handler. If you need more control, consider using a custom Blade directive or helper function instead.

```php

## Common Blade Settings

If multiple controllers are used, extend from a base controller to set common Blade settings:

```php
use wsydney76\blade\Blade;
...
public function beforeAction($action): bool
    {
      
        // Share global settings entry
        Blade::share('settings', Entry::find()->section('settings')->one());

        // Register stringable for DateTime objects
        Blade::stringable(\DateTime::class, function($dateTime) {
            return $dateTime->format('Y-m-d H:i');
        });

        // Datetime directive
        Blade::directive('datetime', function($expression) {
            return "<?php echo ($expression)->format('Y-m-d H:i'); ?>";
        });
        return parent::beforeAction($action);
    }
```

## Clearing Cache

Remove cached Blade templates via console command:

```bash
php craft clear-caches/blade
```

Or via Control Panel: Utilities → Caches → Blade Template Cache

## Configuration

If you want to customize Blade settings, create a config file at `config/_blade.php`.

```php
<?php

use craft\helpers\App;

return [
    'bladeViewsPath' => App::env('BLADE_VIEWS_PATH') ?? '@templates/_blade',
    'bladeCachePath' => App::env('BLADE_CACHE_PATH') ?? '@runtime/blade/cache',
    'bladeComponentPaths' => [
        ['path' => '@templates/_shared', 'prefix' => 'shared'],
    ],
];
```

where:

* `bladeViewsPath` - Path to Blade views directory, defaults to `@root/resources/views`
* `bladeCachePath` - Path to Blade compiled templates cache directory, defaults to `@runtime/blade/cache`
* `bladeComponentPaths` - Additional component paths with (optional) prefixes, defaults to empty array. In this example, you could use `<x-shared:mycomponent />` to reference a component in `@templates/_shared/mycomponent.blade.php`.

Values support Craft aliases.


## Reactive components

Livewire-like reactive components are not supported, as Livewire is deeply bound to core Laravel. 

Consider porting existing components to Twig using Sprig plugin or alike.

Otherwise, you can integrate with Alpine.js (which is used by Livewire behind the scenes) to come somewhat close and keep most of your controller logic and templates.

For convenience, this plugin ships with a copy of a [Craft CMS Action Twig Helper Component](https://github.com/wsydney76/extras/blob/main/ACTIONS.md), stolen from a private plugin.

Pull it in with `@renderTwig('@blade/_actions.twig')`

In this example, you would

* replace `wire:model.live.debounce.500ms="q"` with `x-model="q"` and `@input.debounce.500ms="fetch"`
* add an Alpine.js component that implements `fetch()` and calls the Craft Action to fetch updated results and update the DOM accordingly.
* place the search results in a separate Blade component that can be rendered both on initial page load and via the Action, enabling partial reload.

If required, add handling for browser history updates including `popState` events.

See [docs for helper functions](https://github.com/wsydney76/extras/blob/main/ACTIONS.md#alpine-js-browser-history-management).

### Main Blade Template

Extract JavaScript into your asset bundle as needed.

```blade
@props([
    'entry' => null,
    'q' => '',
    'resultHtml' => '',
])
<x-layout title="Search">
    <h1>{{ ($entry->title ?? null) ?: 'Search' }}</h1>

    <div x-data="searchWidget({
            q: @js($q),
            html: @js($resultHtml)
            })"
         @popstate.window="popState"
         class="space-y-4"
    >
        <form @submit.prevent>
            <input x-model="q"
                   @input.debounce.500ms="fetch"
                   autofocus
                   type="text"
                   name="q"
                   placeholder="Search..."
                   class="w-full"
            >
        </form>

        <div id="results" x-html="html"></div>
    </div>

    @renderTwig('@blade/_actions.twig')

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('searchWidget', ({ q, html }) => ({
                q,
                html,
                searchParams: ['q'],

                fetch(updateHistory = true) {
                    updateHistory && window.Actions.pushState.call(this, this.searchParams);
                    if (!this.q) {
                        this.html = '';
                        return;
                    }
                    window.Actions.postAction('main/search/fetch',
                        { q: this.q },
                        (data) => {
                            this.html = data;
                        },
                    );
                },

                popState() {
                    window.Actions.popState.call(this, this.searchParams, () => this.fetch(false));
                },
            }));
        });
    </script>
</x-layout>
```

### Component Template

```blade
@php($hasQuery = isset($q) && trim($q) !== '')

@if(!empty($items))
    <ul>
        @foreach($items as $item)
            <li>
                <a href="{{ $item->url }}">{{ $item->title }}</a>
            </li>
        @endforeach
    </ul>
@elseif($hasQuery)
    <p>No results found.</p>
@endif

```

### Controller

```php
<?php

namespace modules\main\controllers;

use Craft;
use craft\elements\Entry;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use wsydney76\blade\Blade;

/**
 * Search controller
 */
class SearchController extends Controller
{
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = ['index', 'fetch'];

    public string $q = '';

    public function beforeAction($action): bool
    {
        $this->q = Craft::$app->getRequest()->getParam('q', '');
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return Blade::render('search', [
            'entry' => Craft::$app->urlManager->getMatchedElement(),
            'q' => $this->q,
            'resultHtml' => $this->renderComponent()
        ]);
    }

    public function actionFetch()
    {
        return $this->renderComponent();
    }

    protected function renderComponent()
    {
        $entries = [];
        if (trim($this->q) !== '') {
            $entries = Entry::find()
                ->section('post')
                ->search($this->q)
                ->all();
        }

        return Blade::render('components.search-results', [
            'items' => $entries,
            'q' => $this->q,
        ]);
    }
}

```