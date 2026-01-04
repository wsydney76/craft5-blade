<?php

namespace wsydney76\blade\web\twig;

use craft\helpers\Template;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use wsydney76\blade\Blade;

/**
 * Twig extension that provides Blade rendering capabilities within Twig templates.
 *
 * Exposes:
 * - `renderBlade(view, data = [])` -> returns raw HTML produced by Blade.
 */
class BladeTwigExtension extends AbstractExtension
{
    /**
     * Register custom Twig functions.
     *
     * @return array<TwigFunction> Array of registered Twig functions
     */
    public function getFunctions(): array
    {
        // Define custom Twig functions
        // (see https://twig.symfony.com/doc/3.x/advanced.html#functions)
        return [
            // Returns a Twig Markup/"raw" value so the Blade output isn't double-escaped by Twig.
            // Treat the Blade output as trusted HTML, similar to Twig's `|raw`.
            new TwigFunction('renderBlade', function(string $view, array $data = []) {
               return Template::raw(Blade::renderTemplate($view, $data));
            }),
        ];
    }
}
