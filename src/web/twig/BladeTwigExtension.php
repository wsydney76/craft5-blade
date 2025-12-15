<?php

namespace wsydney76\blade\web\twig;

use Craft;
use craft\helpers\Template;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;
use wsydney76\blade\BladePlugin;

/**
 * Twig extension
 */
class BladeTwigExtension extends AbstractExtension
{

    public function getFunctions()
    {
        // Define custom Twig functions
        // (see https://twig.symfony.com/doc/3.x/advanced.html#functions)
        return [
            new TwigFunction('renderBlade', function($view, $data = []) {
               $blade = BladePlugin::getInstance()->blade;
               return Template::raw($blade->render($view, $data));
            }),
            // ...
        ];
    }

}
