# Eloquent in a Craft (non-Laravel) project

## Illuminate packages

Add these dependencies to `composer.json`:

```json
{
  "require": {
    "illuminate/database": "^10.0",
    "illuminate/pagination": "^10.0"
  }
}
```

## Bootstrapping Eloquent

In a non-Laravel project, you need to bootstrap Eloquent manually in your module/plugin `init()` method.

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
], 'craftcms');

$capsule->setAsGlobal();
$capsule->bootEloquent();
```

Naming the connection (here: `craftcms`) is optional (the default is `default`). Using a named connection lets you target a legacy database or multiple databases. If you use a non-default connection name, set the `$connection` property on your models accordingly.

## Model example

Here’s a simple Eloquent model pointing at an existing Craft table. This example uses Craft’s `assets` table.

```php
<?php
namespace modules\eloquent\models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Assets extends Model
{
    protected $table = 'assets';
    protected $connection = 'craftcms';

    /**
     * Scope a query to only include image assets.
     */
    public function scopeImages(Builder $query): Builder
    {
        return $query->where('kind', 'image');
    }
}
```

## Querying

You can now use Eloquent’s query builder.

Controller:

```php
use modules\mymodule\models\Assets;
...
$images = Assets::images()->orderBy('filename')->get();
```

Blade template:

```blade
@foreach ($images as $image)
    {{ $image->filename }}
@endforeach
```

## Pagination

Because there’s no Laravel HTTP kernel here, you’re responsible for pagination setup (current page, base path, etc.).

This example relies on the `config/general.php` setting `pageTrigger`:

```php
 ->pageTrigger('?page=')
```

The native `paginate()` method won’t work out of the box, so you need to manually create a `LengthAwarePaginator` instance.

One option is to add a macro in your module/plugin bootstrap (`init()` method):

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

    // Rebuild the current URL without the "page" query string parameter
    [$base, $query] = array_pad(explode('?', Craft::$app->request->absoluteUrl, 2), 2, '',);
    parse_str($query, $params);
    unset($params['page']);
    $newQuery = http_build_query($params);
    $path = $newQuery ? "$base?$newQuery" : $base;

    return new LengthAwarePaginator($items, $totalCount, $perPage, $page, [
        'path' => $path,
        'pageName' => 'page',
    ]);
});

```

You can then replace `paginate()` with `asCustomPaginator()` in your queries.

Controller:

```php
public function actionShow()
{
    return View::renderTemplate('test.index', [
        'images' => Assets::images()->orderBy('filename')->asCustomPaginator(8),
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

See `vendor/illuminate/pagination/resources/views/*.blade.php` for reference.

```blade
@if ($paginator->hasPages())
    <nav
        role="navigation"
        aria-label="Pagination Navigation"
        class="flex items-center justify-between"
    >
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="...">
                {!! __('Previous page') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="...">
                {!! __('Previous page') !!}
            </a>
        @endif

        <div>
            {{ __('Page') }} {{ $paginator->currentPage() }} {{ __('of') }} {{ $paginator->lastPage() }}
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="...">
                {!! __('Next page') !!}
            </a>
        @else
            <span class="...">
                {!! __('Next page') !!}
            </span>
        @endif
    </nav>
@endif

```