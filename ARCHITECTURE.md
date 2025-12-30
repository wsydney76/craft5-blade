# Blade plugin architecture

__Note: This document is AI generated, unedited, untested. So may be correct or not.__

This document describes the internal architecture of the **Blade** plugin (`wsydney76/craft5-blade`) for Craft CMS 5.

It’s meant for maintainers and developers who want to understand how the plugin boots an Illuminate/Blade runtime inside Craft, how requests get routed to Blade templates, and where to extend/customize behavior.

## High-level goals

- **Render Laravel Blade templates** (`.blade.php`) as Craft CMS frontend templates.
- **Expose Craft data and conveniences** in Blade templates (Craft “Twig globals”, plus optional helper/filter functions).
- **Bridge Twig ↔ Blade** so either engine can call the other when needed.
- Keep the integration **minimal and explicit** rather than attempting to emulate a full Laravel application.

## Repository layout (plugin)

Key locations in `blade/src/`:

- `BladePlugin.php` — Craft plugin entrypoint; registers events/bridges; loads global helper/filter functions.
- `BladeBootstrap.php` — boots the Illuminate container + view factory + Blade compiler.
- `Blade.php` — static facade/“public API” for rendering and registering directives, components, etc.
- `controllers/BaseBladeController.php` — Craft controller action used to render Blade views via element routing.
- `support/`
  - `CraftContainer.php` — minimal Illuminate container with an application namespace.
  - `BladeDirectives.php` — built-in Blade directives compiled at template compile-time.
  - `BladeIfs.php` — built-in Blade conditionals (`@auth`, `@guest`).
  - `BladeShared.php` — shares Craft’s Twig globals to Blade view scope.
  - `BladeHelpers.php` / `BladeFilters.php` — global PHP functions that mirror Craft Twig functions/filters (experimental).
- `web/twig/BladeTwigExtension.php` — Twig function `renderBlade()`.

## Core runtime components

### 1) `BladePlugin` (Craft plugin entrypoint)

`BladePlugin` is the orchestrator. Its main responsibilities:

- Defines a Craft component named `blade` (see `BladePlugin::config()`), implemented by `BladeBootstrap`.
- Registers event handlers:
  - registers the `@blade` Twig template root
  - hooks into element routing (`Entry::EVENT_SET_ROUTE`) to support `blade:` and `.blade.php` template targets
  - adds a “Blade Template Cache” entry to the “Clear Caches” utility
- Registers the Twig → Blade bridge (`BladeTwigExtension`, providing `renderBlade()`)
- Loads *global* helper/filter files:
  - `support/BladeHelpers.php`
  - `support/BladeFilters.php`
- Registers Blade compile-time directives/conditionals/shared globals:
  - `BladeDirectives::register()`
  - `BladeShared::register()`
  - `BladeIfs::register()`

Important design note: the helper/filter files define **global PHP functions** and are loaded explicitly (not via Composer autoload) so they only run once Craft is initialized.

### 2) `BladeBootstrap` (Illuminate runtime bootstrapper)

`BladeBootstrap` builds the smallest set of Illuminate services that Blade needs:

- An Illuminate container (`CraftContainer`) is set as the global container instance.
- Blade/Laravel facades are pointed at that container (`Facade::setFacadeApplication`).
- A filesystem service (`Illuminate\Filesystem\Filesystem`).
- An event dispatcher (`Illuminate\Events\Dispatcher`).
- A config repository with `view.paths` and `view.compiled` to satisfy Blade/component expectations.
- A `BladeCompiler` configured to compile templates into the configured cache directory.
- A view engine resolver (`EngineResolver`) and view finder (`FileViewFinder`).
- A view factory (`Illuminate\View\Factory`) used to render views.

Settings input:

- `Settings::$bladeViewsPath` — where source templates live (default: `@root/resources/views`).
- `Settings::$bladeCachePath` — where compiled templates are written (default: `@runtime/blade/cache`).
- `Settings::$bladeComponentPaths` — optional directories for anonymous components (mapped via `Blade::anonymousComponentPath`).

At the end of boot, `BladeBootstrap` exposes:

- `render($views, $data)` — render a view name or first-existing from a list.
- `share($key, $value)` — share global data with all views.
- registration helpers: `directive()`, `if()`, `stringable()`, `component()`.

### 3) `CraftContainer` (minimal application namespace)

Some Blade component resolution paths call `$app->getNamespace()`.

Craft isn’t a Laravel application, so `CraftContainer` provides a minimal implementation that returns `App\\`.

If your project stores component classes under a different root namespace, this is where you’d adjust that behavior.

### 4) `Blade` facade (public API for PHP + Blade integration)

`Blade` is a static façade over the plugin’s bootstrapped instance.

It’s the recommended way to interact with the runtime from PHP code:

- `Blade::render($view, $data = [])`
- `Blade::renderLocalized($view, $data = [])` — tries `{siteHandle}.{view}` then `{view}`
- `Blade::directive($name, $handler)`
- `Blade::if($name, $handler)`
- `Blade::share($key, $value)`
- `Blade::component($alias, $class, $prefix = null)`
- `Blade::composer($views, $callback)` / `Blade::creator(...)`
- `Blade::paginate($query, ...)`

This also keeps the rest of the codebase from tightly coupling to the internal `BladeBootstrap` wiring.

## Request and rendering flows

### Flow A: Craft Entry route → Controller → Blade template

This is the preferred “native” integration path for frontend requests, separating logic from presentation.

1. A request resolves to an `Entry` element.
2. Craft dispatches `Entry::EVENT_SET_ROUTE`.
3. `BladePlugin::attachEventHandlers()` implements an event listener that inspects the section template setting (or field settings for Matrix, when applicable).
4. Supported section template values:
   - `action:controller/action`
     - Craft route is set directly to that controller/action.
5. The controller action (your custom controller) calls `Blade::render($view, $data)` and returns HTML.

**Data inputs:** the template will have access to Craft’s shared globals (see “Global variables”).

Additionally, your custom controller action is responsible for providing any context data the Blade template needs (the matched element, related elements, etc.).

### Flow B: Craft Entry route → Blade template

This is an alternative “native” integration path for frontend requests.

1. A request resolves to an `Entry` element.
2. Craft dispatches `Entry::EVENT_SET_ROUTE`.
3. `BladePlugin::attachEventHandlers()` implements an event listener that inspects the section template setting (or field settings for Matrix, when applicable).
4. Supported section template values:
    - `blade:some.view`
        - routed to `"_blade/base-blade/render"` with `view` as route param.
    - `path/to/template.blade.php`
        - Converted to dotted view name (`path.to.template`)
        - routed to `"_blade/base-blade/render"` with `view` as route param.
5. The controller action `BaseBladeController::actionRender($view)` calls `Blade::render($view)` and returns HTML.

**Data inputs:** the template will have access to Craft’s shared globals (see “Global variables”).

`BaseBladeController::actionRender()` injects the matched element into the Blade view context using a variable name derived from the element’s short class name (lowercased):

- `craft\elements\Entry` → `$entry`
- `craft\commerce\elements\Product` → `$product`

So when you route an entry to `blade:blog.show`, your Blade template can typically just use `$entry` without any extra wiring.

If you need *additional* context beyond the matched element (prev/next entries, related elements, etc.), you can

* retrieve that data inside the Blade template using Craft's API or native PHP functions,
* use Blade view composers to attach data to specific views (see below).

### Flow C: Twig template → Blade template (`renderBlade()`)

If you’re still in Twig but want to delegate some rendering to Blade:

1. Twig executes `renderBlade('some.view', {...})`.
2. `BladeTwigExtension` returns `Template::raw(Blade::render(...))`.
3. Twig receives a “raw markup” value and will not escape it again.

This is useful for incremental migrations where Twig remains the primary template engine.

### Flow D: Blade template → Twig template (`@renderTwig`)

If you’re in Blade and want to reuse an existing Twig partial:

1. Blade template uses `@renderTwig('path/to.twig', ['foo' => 'bar'])`.
2. `BladeDirectives` compiles this into PHP that calls `Craft::$app->view->renderTemplate(...)`.
3. The Twig template is rendered by Craft and echoed into the Blade output.

### Flow E: Direct site route → Blade template (`/{routePrefix}/{view}`)

The plugin also registers a plain **site URL rule** that routes matching requests directly to a Blade view via `BaseBladeController::actionRender()`.

Registration happens in `BladePlugin::attachEventHandlers()` via `UrlManager::EVENT_REGISTER_SITE_URL_RULES`.

- Route pattern: `/{routePrefix}/{view}`
- Controller route: `_blade/base-blade/render`
- Settings:
  - `Settings::$routePrefix` (default: `blade`)

Examples (default prefix):

- `/blade/articles` → renders view `articles`
- `/blade/articles/list/bydate` → renders view `articles.list.bydate`

`{view}` is captured as a slash path (so nested URLs work). `BaseBladeController::actionRender()` normalizes it by converting slashes to dots and validating the name to prevent traversal-like input.

Security note: `BaseBladeController` allows anonymous access by default so this can be used for public site templates. If you render sensitive views, lock it down.

### Flow F: Custom controller → Blade template

Custom controller actions can be setup using the usual Craft mechanisms and finally render Blade templates using `Blade::render()`.


## Global variables and shared state

### Craft globals in Blade (`BladeShared`)

`BladeShared::register()` instantiates Craft’s Twig `craft\web\twig\Extension` and calls `getGlobals()`.

Those globals are then shared into Blade using `Blade::share($key, $value)`.

This aims to make Blade templates feel closer to Twig templates by providing variables like:

- `craft`
- `currentUser`
- `currentSite`

Exact globals depend on Craft version and installed plugins.

Performance note: some globals may be “lazy” or trigger service lookups. If this becomes expensive, a future optimization would be to only share a curated subset or share closures.

### Twig-filter/function equivalents (global PHP functions)

The plugin includes experimental global helper/filter functions:

- `support/BladeHelpers.php`
- `support/BladeFilters.php`

These are intended to mirror Craft’s Twig functions and filters but as plain PHP functions usable in Blade.

Implementation pattern (example: filters):

- `BladeFilters.php` constructs a shared `craft\web\twig\Extension` and delegates filter logic to methods like `camelFilter()`, `markdownFilter()`, etc.
- Many functions are guarded with `function_exists()` to reduce collision risk.

Tradeoffs:

- Pros: makes it easier to port Twig-ish template logic into Blade.
- Cons: introduces global functions (potential naming collisions) and not all Twig behaviors map 1:1.

## Built-in directives and conditionals

### Directives (`BladeDirectives`)

Custom directives are compiled at Blade compile-time.

Included directives (non-exhaustive):

- `@renderTwig(...)`
- `@includeLocalized(...)` — tries `{siteHandle}.{view}` then `{view}` using `$__env->first(...)`
- `@requireAdmin`, `@requireLogin`, `@requireGuest`, `@requirePermission(...)` — throw Yii HTTP exceptions
- `@redirect(url, status = 302)` — sets response redirect and terminates request (`Craft::$app->end()`)
- `@set(...)` — arbitrary PHP expression/assignment
- `@paginate(...)` — uses `Blade::paginate()` and injects variables into scope
- `@markdown(...)` — uses global `markdown()` and `purify()` helper functions
- `@header("Header: value")` — sets a response header
- `@cache($options = []) ... @endcache` — template fragment caching using Craft’s TemplateCaches service (Twig `{% cache %}` equivalent)

Security note: directives like `@set` and `@redirect` are powerful. Use them in trusted templates only.

#### Template fragment caching (`@cache ... @endcache`)

The `@cache` directive pair is implemented in `support/BladeDirectives.php` and compiles to the same structure Craft generates for Twig’s `{% cache %}` tag:

- Uses `Craft::$app->getTemplateCaches()` and `Craft::$app->getRequest()`.
- Skips caching for Live Preview and tokenized requests (`getIsLivePreview()` / `getToken()`).
- Wraps the cached block in `ob_start()` / `ob_get_clean()`.
- Saves and returns the fragment with `endTemplateCache(...)`.

Options (`@cache([...])`), all optional:

- `key` (string): override cache key; if omitted, a deterministic key is generated
- `global` (bool): passed through to `getTemplateCache`/`startTemplateCache`/`endTemplateCache` (default `false`)
- `duration` (?string): passed to `endTemplateCache` (default `null`)
- `expiration` (mixed): passed to `endTemplateCache` (default `null`)
- `if` (mixed): only use caching when truthy (Craft Twig `{% cache if ... %}` equivalent)
- `unless` (mixed): only use caching when falsey (Craft Twig `{% cache unless ... %}` equivalent)

Usage:

```blade
@cache
    Hallo
@endcache
```

Under the hood the compiled PHP calls:

- `getTemplateCache($key, $global, true)`
- `startTemplateCache(true, $global)`
- `endTemplateCache($key, $global, $duration, $expiration, $body, true)`

## Template resolution and naming

Blade view names are **dotted** and map to filesystem paths.

Example:

- `Blade::render('blog.entry')`
- looks for `{bladeViewsPath}/blog/entry.blade.php`

Anonymous components can be registered from arbitrary paths via settings `bladeComponentPaths`.

## Caching and invalidation

### Compiled template cache

Blade compiles templates to PHP files stored in `Settings::$bladeCachePath` (default: `@runtime/blade/cache`).

Craft’s “Clear Caches” utility gets a `Blade Template Cache` option that deletes the compiled files.

### What is *not* cached

This plugin doesn’t introduce additional caching layers beyond Blade’s compiled PHP.

For HTML output caching you should use Craft’s standard caching approaches (Twig caches, HTTP caches, reverse proxies) or implement explicit caching in your templates.

## Configuration surface

The plugin reads settings via `BladePlugin::getInstance()->getSettings()`.

Primary settings:

- `bladeViewsPath` — views root
- `bladeCachePath` — compilation cache root
- `bladeComponentPaths` — additional component directories

Because paths are resolved through `craft\helpers\App::parseEnv()`, you can use:

- Yii aliases (e.g. `@root`, `@runtime`)
- environment variables (`$BLADE_CACHE_PATH`, etc.)

## Extension points

Common ways to extend behavior:

- Register directives:
  - `Blade::directive('name', fn($expression) => '...compiled php...');`
- Register conditionals:
  - `Blade::if('feature', fn() => true/false);`
- Share globals:
  - `Blade::share('key', $value);`
- Register class-based components:
  - `Blade::component('alert', \App\View\Components\Alert::class);`
- Register view composers:
  - `Blade::composer('blog.*', function ($view) { ... });`

For project-specific needs (e.g. always providing the current `Entry` as `$entry`), composers are typically the cleanest approach.

## Known tradeoffs / implementation notes

- The integration intentionally **doesn’t emulate** a full Laravel app; it wires only what Blade needs.
- Global function helpers (`BladeHelpers.php`, `BladeFilters.php`) are **experimental** and may diverge from Twig semantics.
- `BaseBladeController` injects the matched element into the view context (e.g. `$entry`, `$product`) but otherwise renders the view by name only; any additional context must be passed explicitly or attached via composers.
- The plugin currently focuses on entry routing. Other element types may work but are not guaranteed without more testing.

## Quick mental model

- Craft handles routing and request lifecycle.
- The plugin translates certain routes into “render a Blade view”.
- Blade runs inside a minimal Illuminate container.
- Blade templates can call into Craft (globals, helpers, Twig rendering) as needed.

## Related docs

- `README.md` — installation and usage notes
- `ai-generated-docs/` — mappings for helper functions/filters (experimental)
