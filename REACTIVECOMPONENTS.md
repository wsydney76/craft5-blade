# Reactive Components

For convenience, this plugin ships with a copy of a [Craft CMS Action Twig Helper Component](https://github.com/wsydney76/extras/blob/main/ACTIONS.md), stolen from a private plugin.

Pull it in with `@renderTwig('@blade/_actions.twig')`

In this example, you would

* replace `wire:model.live.debounce.500ms="q"` with `x-model="q"` and `@input.debounce.500ms="fetch"`
* add an Alpine.js component that implements `fetch()` and calls the Craft Action to fetch updated results and update the DOM accordingly.
* place the search results in a separate Blade component that can be rendered both on initial page load and via the Action, enabling partial reload.

If required, add handling for browser history updates including `popState` events.

See [docs for helper functions](https://github.com/wsydney76/extras/blob/main/ACTIONS.md#alpine-js-browser-history-management).

## Main Blade Template

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

## Component Template

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

## Controller

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