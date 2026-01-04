# Eloquent in a Craft (non-Laravel) project

## Illuminate packages

Add dependencies to  `composer.json`.

```json
{
  "require": {
    "illuminate/database": "^10.0",
    "illuminate/pagination": "^10.0"
  }
}
```

## Bootstrapping Eloquent

In a non-Laravel project, you need to manually bootstrap Eloquent in a module/plugin `init` method.

```php
use Illuminate\Database\Capsule\Manager as Capsule;
...

$capsule = new Capsule();

$capsule->addConnection([
    'driver' => App::env('CRAFT_DB_DRIVER'),
    'host' => App::env('CRAFT_DB_SERVER'),
    'port' => App::env('CRAFT_DB_PORT'),
    'database' => App::env('CRAFT_DB_DATABASE'),
    'username' => App::env('CRAFT_DB_USER'),
    'password' => App::env('CRAFT_DB_PASSWORD'),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();
```

This project uses `illuminate/database` directly (no full Laravel app) and initializes it from the Craft module `modules/eloquent/EloquentModule.php`.

## Model example

Here’s an example Eloquent model for a custom table (using Craft's `assets` table as an example):

```php
<?php

namespace modules\mymodule\models;

use Illuminate\Database\Eloquent\Model;

class Assets extends Model
{
    protected $table = 'assets';
}
```

## Querying 

You can now use Eloquent’s querying capabilities:

Controller:

```php
use modules\mymodule\models\Assets; 
...
$assets = Assets::whereKind('image')->orderBy('filename')->get();
``` 

Blade template:

```blade
@foreach ($images as $image)
    {{ $image->filename }}
@endforeach
```

## Pagination

Because there’s no Laravel HTTP kernel here, you’re responsible for pagination setup (current page, base path etc.).

This example relies on the `config/general.php` setting `pageTrigger`:

```php
 ->pageTrigger('?page=')
```

Controller:

```php
public function actionShow()
{
    $query = Assets::whereKind('image')->orderBy('filename');
    $images = $this->getPaginator($query, 8);

    return View::renderTemplate('test.index', [
        'entry' => Craft::$app->urlManager->getMatchedElement(),
        'images' => $images,
    ]);
}

protected function getPaginator($query, $perPage = 12): LengthAwarePaginator
{
    $page = Craft::$app->request->pageNum;
    $offset = ($page - 1) * $perPage;

    $totalCount = $query->count();
    $items = $query->offset($offset)->limit($perPage)->get();

    $paginator = new LengthAwarePaginator(
        $items, // Collection or array of items for the current page
        $totalCount, // Total item count
        $perPage, // Items per page
        $page, // CURRENT PAGE (this is where it is set)
        [
            'path' => Craft::$app->getRequest()->getAbsoluteUrl(),
            'pageName' => 'page',
        ],
    );
    return $paginator;
}
```

Blade template:

```blade
@foreach ($images as $image)
    {{ $image->filename }}
@endforeach 
{{ $images->links() }}

<div class="mt-8">
    @include('paginator.tailwind', ['paginator' => $images])
</div>
```

Paginator partial (`paginator/simple-tailwind.blade.php`):

See `vendor/illuminate/pagination/resources/views/simple-tailwind.blade.php` for reference.

```blade
@if ($paginator->hasPages())
    <nav
        role="navigation"
        aria-label="Pagination Navigation"
        class="flex items-center justify-between"
    >
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span
                class="relative inline-flex cursor-default items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm leading-5 font-medium text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-600"
            >
                {!! __('Previous page') !!}
            </span>
        @else
            <a
                href="{{ $paginator->previousPageUrl() }}"
                rel="prev"
                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm leading-5 font-medium text-gray-700 ring-gray-300 transition duration-150 ease-in-out hover:text-gray-500 focus:border-blue-300 focus:ring focus:outline-none active:bg-gray-100 active:text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:focus:border-blue-700 dark:active:bg-gray-700 dark:active:text-gray-300"
            >
                {!! __('Previous page') !!}
            </a>
        @endif

        <div>
            {{ __('Page') }} {{ $paginator->currentPage() }} {{ __('of') }}
            {{ $paginator->lastPage() }}
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a
                href="{{ $paginator->nextPageUrl() }}"
                rel="next"
                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm leading-5 font-medium text-gray-700 ring-gray-300 transition duration-150 ease-in-out hover:text-gray-500 focus:border-blue-300 focus:ring focus:outline-none active:bg-gray-100 active:text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:focus:border-blue-700 dark:active:bg-gray-700 dark:active:text-gray-300"
            >
                {!! __('Next page') !!}
            </a>
        @else
            <span
                class="relative inline-flex cursor-default items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm leading-5 font-medium text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-600"
            >
                {!! __('Next page') !!}
            </span>
        @endif
    </nav>
@endif

```