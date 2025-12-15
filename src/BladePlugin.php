<?php

namespace wsydney76\blade;

use Craft;
use craft\base\Element;
use craft\base\Event;
use craft\base\Plugin;
use craft\elements\Entry;
use craft\events\RegisterUrlRulesEvent;
use craft\events\SetElementRouteEvent;
use craft\web\UrlManager;

/**
 * Blade plugin
 *
 * @method static BladePlugin getInstance()
 */
class BladePlugin extends Plugin
{
    public string $schemaVersion = '1.0.0';

    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
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
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/5.x/extend/events.html to get started)

       /* Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES, function (RegisterUrlRulesEvent $event) {
            $event->rules['blade/render/<template:{slug}>'] = '_blade/base-blade/render';
        });*/

        Event::on(
            Entry::class,
            Element::EVENT_SET_ROUTE,
            function(SetElementRouteEvent $event) {

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
                    $template = explode(':', $template)[1];
                    Craft::$app->urlManager->setRouteParams([
                        'template' => $template,
                    ]);
                    $event->route = "_blade/base-blade/render";
                    $event->handled = true;
                }
            });
    }
}
