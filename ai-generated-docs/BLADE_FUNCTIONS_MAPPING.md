# Blade Helper Functions Mapping

__Note: This document is AI generated, unedited, untested. So may be correct or not.__ 

This document maps each helper function in `BladeHelpers.php` to the underlying static class call or method it wraps.

## Helper Functions to Static Class Calls

| Helper Function | Wrapped Static Class Call / Method | Notes |
|----------------|-------------------------------------|-------|
| `actionUrl()` | `UrlHelper::actionUrl()` | |
| `alias()` | `Craft::getAlias()` | |
| `clone_var()` | `clone` keyword | Named differently because `clone` is a reserved keyword |
| `collect()` | `Illuminate\Support\Collection::make()` | Returns `ElementCollection` if all items are elements |
| `configure()` | `Craft::configure()` | |
| `cpUrl()` | `UrlHelper::cpUrl()` | |
| `create()` | `Craft::createObject()` | |
| `dataUrl()` | `Html::dataUrl()` / `Asset::getDataUrl()` | Uses `Asset::getDataUrl()` for Asset objects, otherwise `Html::dataUrl()` |
| `dump()` | `Craft::dump()` | |
| `encodeUrl()` | `UrlHelper::encodeUrl()` | |
| `entryType()` | `Craft::$app->getEntries()->getEntryTypeByHandle()` | |
| `expression()` | `new Expression()` | Constructor wrapper for `yii\db\Expression` |
| `fieldValueSql()` | `$provider->getFieldLayout()->getFieldByHandle()->getValueSql()` | Method chain wrapper |
| `getenv_craft()` | `App::env()` | |
| `gql()` | `Craft::$app->getGql()->executeQuery()` | Also uses `Gql::createFullAccessSchema()` |
| `parseEnv()` | `App::parseEnv()` | |
| `parseBooleanEnv()` | `App::parseBooleanEnv()` | |
| `plugin()` | `Craft::$app->getPlugins()->getPlugin()` | |
| `raw()` | `TemplateHelper::raw()` | |
| `renderObjectTemplate()` | `Craft::$app->getView()->renderObjectTemplate()` | |
| `seq()` | `Sequence::next()` / `Sequence::current()` | Uses `next()` or `current()` based on parameter |
| `shuffle_arr()` | `shuffle()` | Native PHP function wrapper with array conversion |
| `siteUrl()` | `UrlHelper::siteUrl()` | |
| `url()` | `UrlHelper::url()` | |
| `canCreateDrafts()` | `Craft::$app->getElements()->canCreateDrafts()` | |
| `canDelete()` | `Craft::$app->getElements()->canDelete()` | |
| `canDeleteForSite()` | `Craft::$app->getElements()->canDeleteForSite()` | |
| `canDuplicate()` | `Craft::$app->getElements()->canDuplicate()` | |
| `canSave()` | `Craft::$app->getElements()->canSave()` | |
| `canView()` | `Craft::$app->getElements()->canView()` | |
| `actionInput()` | `Html::actionInput()` | |
| `attr()` | `Html::renderTagAttributes()` | |
| `csrfInput()` | `Html::csrfInput()` | |
| `failMessageInput()` | `Html::failMessageInput()` | |
| `hiddenInput()` | `Html::hiddenInput()` | |
| `input()` | `Html::input()` | |
| `ol()` | `Html::ol()` | |
| `redirectInput()` | `Html::redirectInput()` | |
| `successMessageInput()` | `Html::successMessageInput()` | |
| `svg()` | `Html::svg()` | May also use `Html::modifyTagAttributes()` for class parameter |
| `tag()` | `Html::tag()` | Also uses `Html::encode()` and `ArrayHelper::remove()` |
| `ul()` | `Html::ul()` | |
| `head()` | `Craft::$app->getView()->head()` | DOM event wrapper |
| `beginBody()` | `Craft::$app->getView()->beginBody()` | DOM event wrapper |
| `endBody()` | `Craft::$app->getView()->endBody()` | DOM event wrapper |

## Helper Classes Used

- `craft\helpers\UrlHelper` - URL generation
- `craft\helpers\Html` - HTML generation
- `craft\helpers\App` - Environment variable handling
- `craft\helpers\Template` (as `TemplateHelper`) - Template helpers
- `craft\helpers\Sequence` - Sequence generation
- `craft\helpers\Gql` - GraphQL helpers
- `craft\helpers\ArrayHelper` - Array manipulation
- `Craft` - Main Craft application and factory methods
- `yii\db\Expression` - Database expression wrapper
- `Illuminate\Support\Collection` - Laravel collection implementation

## Categories

### URL Helpers
- `actionUrl()`, `cpUrl()`, `encodeUrl()`, `siteUrl()`, `url()`

### HTML Generation
- `actionInput()`, `attr()`, `csrfInput()`, `failMessageInput()`, `hiddenInput()`, `input()`, `ol()`, `redirectInput()`, `successMessageInput()`, `svg()`, `tag()`, `ul()`

### Element Authorization
- `canCreateDrafts()`, `canDelete()`, `canDeleteForSite()`, `canDuplicate()`, `canSave()`, `canView()`

### DOM Events
- `head()`, `beginBody()`, `endBody()`

### Utility Functions
- `alias()`, `clone_var()`, `collect()`, `configure()`, `create()`, `dataUrl()`, `dump()`, `entryType()`, `expression()`, `fieldValueSql()`, `getenv_craft()`, `gql()`, `parseEnv()`, `parseBooleanEnv()`, `plugin()`, `raw()`, `renderObjectTemplate()`, `seq()`, `shuffle_arr()`

