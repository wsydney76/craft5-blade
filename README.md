# Blade

Enables Laravel Blade templates for Craft CMS, providing a modern templating engine alternative to Twig.

## Requirements

This plugin requires Craft CMS 5.8.0 or later, and PHP 8.2 or later.

## Installation

Install the plugin via Composer:

```bash
composer require wsydney76/craft5-blade
php craft plugin/install _blade
```

## Features

- **Full Blade syntax support** - Use all Laravel Blade features including components, directives, and control structures
- **Blade components** - Create and use reusable components with props
- **Custom directives** - Define custom Blade directives for your application
- **Twig integration** - Call Twig templates from Blade using the `@renderTwig()` directive
- **Global data sharing** - Access Craft globals in Blade templates (like `craft`, site name, etc.)
- **Template inheritance** - Use Blade's powerful layout system with `@extends` and `@section`

## Limitations

- Does not support Laravel-specific helper functions and blade directives that depend on Laravel features not present in Craft CMS.
- Does not offer equivalent functionality for advanced Craft Twig features, e.g.`cache` or `paginate` twig tags.
- There is no direct equivalent for Twig filters; in most cases PHP functions can be used instead.
- For now, only used for entry element type. Should work with other element types but not yet tested.
- IDE support for Blade templates in Craft projects may be limited compared to Twig, e.g. there is no code completion for custom fields.
- Not performance optimized for large scale applications; suitable for small to medium sites.

## Usage

### Basic Setup

Create your Blade templates in the `resources/views` directory (or set the `BLADE_VIEWS_PATH` environment variable).

Template cache is stored in `storage/runtime/blade/cache` (or set `BLADE_CACHE_PATH` environment variable).

### Creating Blade Templates

Create `.blade.php` files in your views directory:

```blade
@props([
    'name' => 'Guest',
    'entries'
])

@php
    /** @var \craft\elements\Entry $entry */
@endphp

<x-layout title="Welcome">
    <h1>Hello, {{ $name }}</h1>

    <h1>Site: {{ $systemName }}</h1>

    @foreach ($entries as $entry)
        <div>
            <h2>{{ $entry->title }}</h2>
            <p>Posted: {{ $entry->postDate->format('Y-m-d') }}</p>
        </div>
    @endforeach
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
    <title>{{ $title }}</title>
    @renderTwig('_layouts/nav.twig')
</head>
<body>
    <main>
        {{ $slot }}
    </main>
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
</x-layout>
```

### Routing for Craft entries

In order to use Blade templates for Craft entries, set the template in the section settings

* to a custom controller action that renders a Blade template: `action:main/blog/show`

```php
public function actionShow() {
    $blade = BladePlugin::getInstance()->blade;

    return $blade->render('blog.show');
}
```

* or to a Blade template directly: `blade:blog.show`

In both cases, the entry will be available in the template as `$entry` via `Craft::$app->urlManager->getMatchedElement()`.

### Rendering from PHP

Render Blade templates from plugins or controllers:

```php

BladePlugin::getInstance()->blade->render('mytemplate', [
    'entries' => $entries
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

### Accessing Craft Globals

All Craft globals are automatically available in Blade templates:

```blade
<p>App Name: {{ $systemName }}</p>
<p>Site URL: {{ $siteUrl }}</p>
<p>User name: {{ $currentUser->name }}</p>
<p>Craft variable: {{ $craft->app->language }}</p>
```

Additionally, the entry matched by the URL is available as `$entry` when rendering Craft entries (or null, if there is none).

## Custom Directives

Define custom Blade directives in your plugin or module:

```php
BladePlugin->getInstance()->blade->directive('datetime', function($expression) {
    return "<?php echo ($expression)->format('Y-m-d H:i'); ?>";
})
```

## Shared Data

Share global data across all Blade templates:

```php
BladePlugin->getInstance()->blade->share('settings', Entry::find()->section('settings')->one());
```

Then access it in any Blade template:
```

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

