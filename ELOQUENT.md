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
$images = Assets::whereKind('image')->orderBy('filename')->get();
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

The native `paginate()` method won’t work out of the box, so you need to manually create a `LengthAwarePaginator` instance.

This can be done by adding a macro in your Module/Plugin bootstrap (init method):

```php
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
...

Builder::macro('asCustomPaginator', function ($perPage = 12) {
    /** @var Builder $this */
    $page = Craft::$app->request->pageNum;
    $offset = ($page - 1) * $perPage;
    $totalCount = $this->count();
    $items = $this->offset($offset)->limit($perPage)->get();

    return new LengthAwarePaginator($items, $totalCount, $perPage, $page, [
        'path' => Craft::$app->getRequest()->getAbsoluteUrl(),
        'pageName' => 'page',
    ]);
});

```

Now you can replace `paginate()` with `asCustomPaginator` method in your queries.

Controller:

```php
public function actionShow()
{
    return View::renderTemplate('test.index', [
        'images' => Assets::whereKind('image')->orderBy('filename')->asCustomPaginator(8),
    ]);
}
```

Blade template:

```blade
@foreach ($images as $image)
    {{ $image->filename }}
@endforeach 

<div class="mt-8">
    @include('paginator.simple-tailwind', ['paginator' => $images])
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