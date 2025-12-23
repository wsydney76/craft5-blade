# Blade Functions Quick Reference

__Note: This document is AI generated, unedited, untested. So may be correct or not.__

A quick reference guide for all Blade helper functions available in BladeHelpers.php.

## URL & Navigation Functions

### URL Generation
```
actionUrl($path, $params, $scheme)      - Generate action URL
cpUrl($path, $params, $scheme)          - Generate control panel URL
siteUrl($path, $params, $scheme, $siteId) - Generate site URL
url($path, $params, $scheme)            - Generate URL
encodeUrl($url)                         - Encode URL for safe output
```

## Configuration & Environment Functions

```
alias($alias)                           - Get Craft alias (e.g., @web, @root)
getenv_craft($name, $default)           - Get environment variable
parseEnv($value)                        - Parse environment variable value
parseBooleanEnv($value)                 - Parse boolean environment variable
```

## Object & Creation Functions

```
configure($object, $properties)         - Configure object properties
create($config)                         - Create object from config array
clone_var($var)                         - Clone a variable (clone is reserved)
expression($expression, $params, $config) - Create database expression
entryType($handle)                      - Get entry type by handle
plugin($handle)                         - Get plugin instance by handle
```

## Collection & Array Functions

```
collect($var)                           - Create collection (returns ElementCollection for elements)
shuffle_arr($arr)                       - Shuffle array randomly
fieldValueSql($provider, $fieldHandle, $key) - Get field value SQL
```

## GraphQL Functions

```
gql($query, $variables, $operationName) - Execute GraphQL query with full access
```

## Sequence Functions

```
seq($name, $length, $next)              - Get next or current sequence value
```

## Template Rendering Functions

```
renderObjectTemplate($template, $object) - Render template with object context
raw($string)                            - Mark string as safe HTML
```

## Element Authorization Functions

### Check Permissions
```
canCreateDrafts($element, $user)        - Check if can create drafts
canDelete($element, $user)              - Check if can delete element
canDeleteForSite($element, $user)       - Check if can delete for site
canDuplicate($element, $user)           - Check if can duplicate element
canSave($element, $user)                - Check if can save element
canView($element, $user)                - Check if can view element
```

## HTML Generation Functions

### Form Inputs
```
actionInput($action, $params)           - Generate hidden action input
csrfInput()                             - Generate CSRF token input
hiddenInput($name, $value, $options)    - Generate hidden input
input($type, $name, $value, $options)   - Generate input element
failMessageInput($message)              - Generate fail message input
successMessageInput($message)           - Generate success message input
redirectInput($url)                     - Generate redirect input
```

### HTML Elements
```
attr($attributes)                       - Render HTML attributes string
tag($type, $attributes)                 - Generate HTML tag
ol($items, $attributes)                 - Generate ordered list
ul($items, $attributes)                 - Generate unordered list
svg($svg, $sanitize, $namespace, $class) - Generate SVG element
```

## DOM Event Functions

### Page Lifecycle (no-op in Blade)
```
head()                                  - Register head content
beginBody()                             - Begin body event
endBody()                               - End body event
```

## Debug Functions

```
dump(...$vars)                          - Dump variables for debugging
```

## Translation Functions

```
__($message, $category, $params, $language) - Translate message
```

---

## Usage Examples by Category

### URL Generation Examples
```blade
href="{{ siteUrl('blog') }}"
href="{{ cpUrl('entries') }}"
href="{{ actionUrl('users/logout') }}"
href="{{ url('about') }}"
```

### Configuration Examples
```blade
{{ alias('@web') }}                     {{-- Web root path --}}
{{ getenv_craft('APP_ENV', 'production') }}
{{ parseEnv('$APP_KEY') }}
```

### Object Creation Examples
```blade
@php
  $config = ['class' => 'MyClass', 'property' => 'value'];
  $object = create($config);
  
  $entryType = entryType('blog');
  
  $myPlugin = plugin('my-plugin');
@endphp
```

### Collections Examples
```blade
@php
  $collection = collect($items);  {{-- ElementCollection if all items are elements --}}
  $shuffled = shuffle_arr($items);
@endphp
```

### Element Authorization Examples
```blade
@if (canView($entry))
  {{-- Show entry --}}
@endif

@if (canDelete($entry))
  <button>Delete</button>
@endif

@foreach ($entries as $entry)
  @if (canEdit($entry))
    <a href="{{ $entry.cpEditUrl }}">Edit</a>
  @endif
@endforeach
```

### HTML Generation Examples
```blade
{{-- Form Inputs --}}
{{ csrfInput() }}
{{ hiddenInput('redirect', $redirect) }}
{{ actionInput('users/logout') }}

{{-- HTML Elements --}}
{{ tag('div', ['class' => 'container', 'html' => 'Content']) }}
{{ ol(['Item 1', 'Item 2', 'Item 3']) }}
{{ ul(['Item 1', 'Item 2', 'Item 3']) }}
{{ svg('/images/icon.svg', true) }}
{{ attr(['id' => 'my-id', 'class' => 'active']) }}
```

### Translation Examples
```blade
{{ __('Hello, world!') }}
{{ __('Username', 'site', [], 'en') }}
{{ __('Error message', 'errors') }}
```

### Debug Examples
```blade
{{ dump($variable) }}
{{ dump($var1, $var2, $var3) }}
```

---

## Function Signatures

### URL Functions
```php
actionUrl(string $path = '', array $params = [], ?string $scheme = null): string
cpUrl(string $path = '', array $params = [], ?string $scheme = null): string
siteUrl(string $path = '', array $params = [], ?string $scheme = null, ?string $siteId = null): string
url(string $path = '', array $params = [], ?string $scheme = null): string
encodeUrl(string $url): string
```

### Configuration Functions
```php
alias(string $alias): string
getenv_craft(string $name, mixed $default = null): mixed
parseEnv(string $value): string
parseBooleanEnv(string $value): bool
```

### Object Functions
```php
configure(object $object, array $properties): object
create(array|string $config): object
clone_var(mixed $var): mixed
expression(mixed $expression, array $params = [], array $config = []): Expression
entryType(string $handle): EntryType
plugin(string $handle): ?PluginInterface
```

### Collection Functions
```php
collect(mixed $var): Collection|ElementCollection
shuffle_arr(iterable $arr): array
fieldValueSql(FieldLayoutProviderInterface $provider, string $fieldHandle, ?string $key = null): ?string
```

### GraphQL Functions
```php
gql(string $query, ?array $variables = null, ?string $operationName = null): array
```

### Sequence Functions
```php
seq(string $name, ?int $length = null, bool $next = true): int|string
```

### Template Functions
```php
renderObjectTemplate(string $template, mixed $object): string
raw(string $string): string
```

### Authorization Functions
```php
canCreateDrafts(ElementInterface $element, ?User $user = null): bool
canDelete(ElementInterface $element, ?User $user = null): bool
canDeleteForSite(ElementInterface $element, ?User $user = null): bool
canDuplicate(ElementInterface $element, ?User $user = null): bool
canSave(ElementInterface $element, ?User $user = null): bool
canView(ElementInterface $element, ?User $user = null): bool
```

### Form/HTML Functions
```php
actionInput(string $action, array $params = []): string
csrfInput(): string
hiddenInput(string $name, string $value = null, array $options = []): string
input(string $type, string $name = null, string $value = null, array $options = []): string
failMessageInput(string $message = null): string
successMessageInput(string $message = null): string
redirectInput(string $url): string
attr(array $attributes): string
tag(string $type, array $attributes = []): string
ol(array $items, array $attributes = []): string
ul(array $items, array $attributes = []): string
svg(Asset|string $svg, ?bool $sanitize = null, ?bool $namespace = null, ?string $class = null): string
```

### DOM Functions
```php
head(): void
beginBody(): void
endBody(): void
```

### Debug Functions
```php
dump(...$vars): string
```

### Translation Functions
```php
__($message, $category = null, $params = null, ?string $language = null): string
```

---

## Function Categories at a Glance

| Category | Count | Functions |
|----------|-------|-----------|
| URL & Navigation | 5 | actionUrl, cpUrl, siteUrl, url, encodeUrl |
| Configuration | 4 | alias, getenv_craft, parseEnv, parseBooleanEnv |
| Object & Creation | 6 | configure, create, clone_var, expression, entryType, plugin |
| Collections & Arrays | 3 | collect, shuffle_arr, fieldValueSql |
| GraphQL | 1 | gql |
| Sequences | 1 | seq |
| Template Rendering | 2 | renderObjectTemplate, raw |
| Authorization | 6 | canCreateDrafts, canDelete, canDeleteForSite, canDuplicate, canSave, canView |
| Form Inputs | 7 | actionInput, csrfInput, hiddenInput, input, failMessageInput, successMessageInput, redirectInput |
| HTML Elements | 5 | attr, tag, ol, ul, svg |
| DOM Events | 3 | head, beginBody, endBody |
| Debug | 1 | dump |
| Translation | 1 | __ |
| **TOTAL** | **45+** | |

---

## Common Use Cases

### Generating Forms
```blade
<form method="post" action="{{ actionUrl('posts/save') }}">
  {{ csrfInput() }}
  {{ hiddenInput('redirect', '/blog') }}
  {{ input('text', 'title', '', ['class' => 'form-control']) }}
  <button type="submit">Save</button>
</form>
```

### Conditional Rendering Based on Permissions
```blade
@foreach ($entries as $entry)
  <div>
    <h3>{{ $entry->title }}</h3>
    @if (canView($entry))
      <p>{{ $entry->description }}</p>
    @endif
    @if (canDelete($entry))
      <a href="{{ actionUrl('entries/delete', {id: entry.id}) }}">Delete</a>
    @endif
  </div>
@endforeach
```

### Environment Configuration
```blade
@if (getenv_craft('APP_DEBUG'))
  {{ dump($debugInfo) }}
@endif

Database: {{ getenv_craft('DB_DATABASE', 'craft') }}
```

### Creating Collections
```blade
@php
  $items = collect($entries);
  $shuffled = collect($entries)->shuffle();
@endphp

@foreach ($items as $item)
  {{-- Render item --}}
@endforeach
```

### Generating Lists
```blade
{{ ol(['First item', 'Second item', 'Third item'], ['class' => 'custom-list']) }}

{{ ul($items->pluck('title')->all(), ['id' => 'nav']) }}
```

### Translation Examples
```blade
<h1>{{ __('Welcome to our site') }}</h1>

<p>
  {{ __('Hello, {name}!', 'site', ['name' => $user->name]) }}
</p>

<button>{{ __('Delete', 'buttons') }}</button>
```

---

## Notes

- **User Context:** Authorization functions (`canCreate`, `canDelete`, etc.) can accept an optional user parameter. If omitted, the current user is used.
- **Aliases:** Common aliases are `@web`, `@root`, `@basePath`, `@storage`. See Craft documentation for complete list.
- **Collections:** Use `collect()` to create Illuminate collections, which has many helpful methods like `map()`, `filter()`, `pluck()`, etc.
- **HTML Safety:** Use `raw()` to mark strings as safe HTML when they contain markup.
- **Forms:** Always include `csrfInput()` in forms. Use `failMessageInput()` and `successMessageInput()` with form redirects.
- **Translation:** The `__()` function defaults to 'site' category if not specified.

---

## See Also

- `BLADE_FILTERS_QUICK_REFERENCE.md` - Filter functions
- `HELPER_FUNCTIONS_MAPPING.md` - Detailed helper function mapping
- `README.md` - Plugin overview

