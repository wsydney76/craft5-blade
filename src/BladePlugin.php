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
use craft\helpers\App;
use craft\helpers\FileHelper;
use craft\utilities\ClearCaches;
use craft\web\View;
use wsydney76\blade\models\Settings;
use wsydney76\blade\support\BladeDirectives;
use wsydney76\blade\support\BladeIfs;
use wsydney76\blade\support\BladeShared;
use wsydney76\blade\web\twig\BladeTwigExtension;
use yii\base\ErrorException;

/**
 * Blade plugin
 *
 * @method static BladePlugin getInstance()
 */
class BladePlugin extends Plugin
{
    public string $schemaVersion = '1.0.0';

    public bool $hasCpSection = true;

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

        $this->attachEventHandlers();

        // Any code that creates an element query or loads Twig should be deferred until
        // after Craft is fully initialized, to avoid conflicts with other plugins/modules
        Craft::$app->onInit(function() {
            // ...
        });

        Craft::$app->view->registerTwigExtension(new BladeTwigExtension());

        BladeDirectives::register();
        BladeShared::register();
        BladeIfs::register();
    }

    /**
     * Attach plugin event handlers.
     *
     * Registers handlers for:
     * - Template root registration
     * - Element route resolution (supports blade: and action: prefixes)
     * - Cache clearing utility integration
     */
    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/5.x/extend/events.html to get started)

        Event::on(
            View::class,
            View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS,
            function(RegisterTemplateRootsEvent $event): void {
                $event->roots['@blade'] = __DIR__ . '/templates';
            }
        );

        Event::on(
            Entry::class,
            Element::EVENT_SET_ROUTE,
            function(SetElementRouteEvent $event): void {

                /** @var Entry $entry */
                $entry = $event->sender;

                $template = $entry->section ?
                    // craft\elements\Entry::route()
                    $entry->section->getSiteSettings()[$entry->siteId]->template ?? null :
                    // craft\fields\Matrix::getRouteForElement()
                    $entry->field->siteSettings[$entry->site->uid]['template'] ?? '';

                if (!$template) {
                    return;
                }

                if (str_starts_with($template, 'action:')) {
                    // Assume the setting is correct, will throw an error anyway if not
                    $action = explode(':', $template)[1];
                    $event->route = $action;
                    $event->handled = true;
                }

                if (str_starts_with($template, 'blade:')) {
                    // Assume the setting is correct, will throw an error anyway if not
                    $view = explode(':', $template)[1];
                    Craft::$app->urlManager->setRouteParams([
                        'view' => $view,
                    ]);
                    $event->route = "_blade/base-blade/render";
                    $event->handled = true;
                }

                if (str_ends_with($template, '.blade.php')) {
                    $view = str_replace('.blade.php', '', $template);
                    $view = str_replace('/', '.', $view);
                    Craft::$app->urlManager->setRouteParams([
                        'view' => $view,
                    ]);
                    $event->route = "_blade/base-blade/render";
                    $event->handled = true;
                }
            });

        Event::on(
            ClearCaches::class,
            ClearCaches::EVENT_REGISTER_CACHE_OPTIONS,
            function(RegisterCacheOptionsEvent $event): void {
                // Register caches for the Clear Cache Utility
                $event->options = array_merge(
                    $event->options,
                    [
                        [
                            'key' => 'blade',
                            'label' => 'Blade Template Cache',
                            'action' => [$this, 'clearCache'],
                            'info' => 'Clears the compiled Blade template cache files.',
                        ]
                    ]
                );
            }
        );
    }


    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }


    /**
     * Clear the compiled Blade template cache.
     *
     * @throws ErrorException
     */
    public function clearCache(): void
    {
        // template caches
        $dir = App::parseEnv('$BLADE_CACHE_PATH') ?: App::parseEnv('@runtime/blade/cache');
        if (!is_dir($dir)) {
            return;
        }
        FileHelper::clearDirectory($dir);
    }
}
