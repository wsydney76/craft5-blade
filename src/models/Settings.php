<?php

namespace wsydney76\blade\models;

use Craft;
use craft\base\Model;

/**
 * Settings model
 */
class Settings extends Model
{

    public string $bladeViewsPath = '@root/resources/views';
    public string $bladeCachePath = '@runtime/blade/cache';
    public string $bladeViewsSubdir = '';
    public array $bladeComponentPaths = [];

    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            // ...
        ]);
    }
}
