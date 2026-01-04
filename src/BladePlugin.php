<?php

namespace wsydney76\blade;

use Craft;
use craft\base\Element;
use craft\base\Event;
use craft\base\Model;
use craft\base\Plugin;
use craft\elements\Entry;
use craft\events\RegisterCacheOptionsEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\SetElementRouteEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\App;
use craft\helpers\FileHelper;
use craft\utilities\ClearCaches;
use craft\web\UrlManager;
use craft\web\View;
use wsydney76\blade\models\Settings;
use wsydney76\blade\support\BladeDirectives;
use wsydney76\blade\support\BladeIfs;
use wsydney76\blade\support\BladeShared;
use wsydney76\blade\support\BladeStringables;
use wsydney76\blade\support\BladeComponents;
use wsydney76\blade\support\BladeViewComposers;
use wsydney76\blade\web\twig\BladeTwigExtension;
use yii\base\ErrorException;

/**
 * Main Craft CMS plugin class for Blade.
 *
 * Responsibilities:
 * - Boots the Illuminate/Blade runtime via the `blade` component (see `BladeBootstrap`).
 * - Registers Twig integration (`renderBlade`) so Twig templates can delegate to Blade.
 * - Registers Blade directives/conditionals and exposes Craft globals/helpers/filters to Blade.
 * - Hooks into element routing to support `blade:` and `action:` template targets.
 * - Adds a "Blade Template Cache" option to Craft's "Clear Caches" utility.
 *
 * @method static BladePlugin getInstance()
 */
class BladePlugin extends Plugin
{
    /**
     * Craft schema version for project config / migrations.
     */
    public string $schemaVersion = '1.0.0';

    public bool $hasCpSettings = true;

    public static function config(): array
    {
        return [
            'components' => [
                'blade' => [
                    'class' => BladeBootstrap::class,
                ],
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        // Register Craft event handlers (routing, template roots, clear-caches integration).
        $this->attachEventHandlers();

        // Any code that creates an element query or loads Twig should be deferred until
        // after Craft is fully initialized, to avoid conflicts with other plugins/modules.
        Craft::$app->onInit(function() {
            // Provide a small Twig bridge so Twig templates can call `{{ renderBlade('view', {...}) }}`.
            Craft::$app->view->registerTwigExtension(new BladeTwigExtension());

            // Note: We purposely don't rely on Composer autoload for these files.
            // They define global functions (helpers/filters) which depend on Craft being initialized.
            require_once 'support/BladeHelpers.php';
            require_once 'support/BladeFilters.php';

            // Register Blade compile-time directives and runtime conveniences.
            BladeDirectives::register();
            BladeShared::register();
            BladeIfs::register();
            BladeStringables::register();
            BladeComponents::register();
            BladeViewComposers::register();
        });
    }

    /**
     * Attach plugin event handlers.
     *
     * Registers handlers for:
     * - Template root registration (`@blade`)
     * - Element route resolution (supports `blade:` and `action:` prefixes, plus `.blade.php` templates)
     * - Cache clearing utility integration
     */
    private function attachEventHandlers(): void
    {
        // Allow Twig to resolve `@blade/...` templates from this plugin's `src/templates` folder.
        Event::on(
            View::class,
            View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS,
            function(RegisterTemplateRootsEvent $event): void {
                $event->roots['@blade'] = __DIR__ . '/templates';
            }
        );

        // Register a simple site route so you can render a Blade view directly by URL.
        //
        // Examples (default prefix: `blade`):
        // - `/blade/articles` => `articles`
        // - `/blade/articles/list/bydate` => `articles.list.bydate`
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function(RegisterUrlRulesEvent $event): void {
                $prefixes = explode(',', $this->getSettings()->bladeRoutePrefixes);
                foreach ($prefixes as $prefix) {
                    // `{view}` is captured as a slash-delimited path; the controller will sanitize it.
                    $event->rules[$prefix . '/<view:.+>'] = [
                        'route' => '_blade/base-blade/render-template',
                        'params' => [
                            'prefix' => $prefix,
                        ],
                    ];
                }
            }
        );

        // Intercept element route resolution so entries can point at Blade templates.
        //
        // Supported section template formats:
        // - `action:controller/action`   -> route directly to an action.
        // - `blade:some.view`            -> render `resources/views/some/view.blade.php`.
        // - `path/to/view.blade.php`     -> convenience: converts to dotted form `path.to.view`.
        //
        // The controller `_blade/base-blade/render` expects a `view` route param.
        Event::on(
            Entry::class,
            Element::EVENT_SET_ROUTE,
            function(SetElementRouteEvent $event): void {
                /** @var Entry $entry */
                $entry = $event->sender;

                // Craft can resolve routes from:
                // - section site settings (normal entries)
                // - field site settings (e.g. Matrix block route handling)
                $template = $entry->section ?
                    // craft\elements\Entry::route()
                    $entry->section->getSiteSettings()[$entry->siteId]->template ?? null :
                    // craft\fields\Matrix::getRouteForElement()
                    $entry->field->siteSettings[$entry->site->uid]['template'] ?? '';

                if (!$template) {
                    return;
                }

                // `action:` -> bypass Blade, run controller action directly.
                if (str_starts_with($template, 'action:')) {
                    // Assume the setting is correct; Craft will throw if the route is invalid.
                    $action = explode(':', $template)[1];
                    $event->route = $action;
                    $event->handled = true;
                }

                // `blade:` -> dotted view name, rendered by BaseBladeController.
                if (str_starts_with($template, 'blade:')) {
                    $view = explode(':', $template)[1];
                    Craft::$app->urlManager->setRouteParams([
                        'view' => $view,
                    ]);
                    $event->route = "_blade/base-blade/render-template";
                    $event->handled = true;
                }

                // Convenience: treat `.blade.php` file paths as Blade view names.
                // Example: `blog/entry.blade.php` -> `blog.entry`.
                if (str_ends_with($template, '.blade.php')) {
                    $view = str_replace('.blade.php', '', $template);
                    $view = str_replace('/', '.', $view);
                    Craft::$app->urlManager->setRouteParams([
                        'view' => $view,
                    ]);
                    $event->route = "_blade/base-blade/render-template";
                    $event->handled = true;
                }
            }
        );

        // Expose a dedicated cache clear option for compiled Blade templates.
        Event::on(
            ClearCaches::class,
            ClearCaches::EVENT_REGISTER_CACHE_OPTIONS,
            function(RegisterCacheOptionsEvent $event): void {
                $event->options = array_merge(
                    $event->options,
                    [
                        [
                            'key' => 'blade',
                            'label' => 'Blade Template Cache',
                            'action' => [$this, 'clearCache'],
                            'info' => 'Clears the compiled Blade template cache files.',
                        ],
                    ]
                );
            }
        );
    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate(
            '_blade/_settings',
            [
                'settings' => $this->getSettings(),
                'config' => Craft::$app->getConfig()->getConfigFromFile('_blade')
            ]
        );
    }

    /**
     * Clear the compiled Blade template cache.
     *
     * This deletes the compiled PHP files generated by Blade (not the source templates).
     * The directory is resolved from `$BLADE_CACHE_PATH` if set, otherwise defaults to
     * Craft's runtime path `@runtime/blade/cache`.
     *
     * Note: This is intentionally conservative. If the directory doesn't exist, it returns early.
     *
     * @throws ErrorException If the cache directory exists but can't be cleared.
     */
    public function clearCache(): void
    {
        $dir = App::parseEnv('$BLADE_CACHE_PATH') ?: App::parseEnv('@runtime/blade/cache');
        if (!is_dir($dir)) {
            return;
        }
        FileHelper::clearDirectory($dir);
    }
}
