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
- Does not offer equivalent functionality for advanced Craft Twig features, e.g.`cache` twig tag.
- There is no direct equivalent for Twig filters; in most cases PHP functions can be used instead.
- Does not fully support [Template localization](https://craftcms.com/docs/5.x/development/templates.html#template-localization)
- For now, only used for entry element type. Should work with other element types but not yet tested.
- IDE support for Blade templates in Craft projects may be limited compared to Twig, e.g. there is no code completion for custom fields.
- The central BladeBootstrap.php class is mostly AI generated and may look like a complete mess for Laravel/Blade experts. But it works for the tested use cases...
- Not yet reviewed in terms of performance/memory usage.

## Usage

### Basic Setup

Create your Blade templates in the `resources/views` directory (or set the `BLADE_VIEWS_PATH` environment variable).

Template cache is stored in `storage/runtime/blade/cache` (or set `BLADE_CACHE_PATH` environment variable).

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

        <div class="mx-auto mt-8">
            <x-blocks :blocks="$entry->bodyContent->all()" />
        </div>
    </article>
</x-layout>
```

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

* or to a Blade template directly: `blade:blog.show`

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
```

## Custom Directives

Define custom Blade directives in your plugin or module:

```php
Blade::directive('datetime', function($expression) {
    return "<?php echo ($expression)->format('Y-m-d H:i'); ?>";
})
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

## Common Blade Settings

If multiple controllers are used, extend from a base controller to set common Blade settings:

```php
use wsydney76\blade\Blade;
...
public function beforeAction($action): bool
    {
      
        // Share global settings entry
        Blade::share('settings', Entry::find()->section('settings')->one());

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

### Environment Variables

Configure paths via environment variables:

```
BLADE_VIEWS_PATH=/path/to/views
BLADE_CACHE_PATH=/path/to/cache
```

Defaults, assuming DDEV setup:
- `BLADE_VIEWS_PATH`: `/var/www/html/resources/views`
- `BLADE_CACHE_PATH`: `/var/www/html/storage/runtime/blade/cache`

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