# A custom field for Filament for picking routes

## Installation

Install this package using composer:

```bash
composer require wotz/filament-link-picker
```

In an effort to align with Filament's theming methodology you will need to use a custom theme to use this plugin.

> **Note**
> If you have not set up a custom theme and are using a Panel follow the instructions in the [Filament Docs](https://filamentphp.com/docs/3.x/panels/themes#creating-a-custom-theme) first. The following applies to both the Panels Package and the standalone Forms package.

1. Import the plugin's views (if not already included) into your theme's css file.

```css

@source '../../../../vendor/wotz/filament-link-picker/resources/views/**/*.blade.php';
```

## Basic usage, simple routes without parameters

The package adds a `linkPicker()` macro to the Route facade of Laravel, this means you can use it in your routes files like this:

```php
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'show'])
    ->name('home')
    ->linkPicker();
```

Important note: your route **must** have a `name` defined, otherwise the link picker will not work.

## Adding the 'external URL' link

If you want to use the "Exernal link" option in the link picker, you need to add a route like this, in your `AppServiceProvider`:

```php
LinkCollection::addExternalLink();
```

Or change some of the default values:
```php
LinkCollection::addExternalLink(
    routeName: 'external',
    group: 'General',
    label: 'External URL',
    description: 'Redirects to an external URL',
);
```

## Adding the 'mailto' link

If you want to add a "mailto:" option in the link picker, you need to add a route like this, in your `AppServiceProvider`:

```php
LinkCollection::addEmailLink();
```

Or change some of the default values:
```php
LinkCollection::addEmailLink(
    routeName: 'email',
    group: 'General',
    label: 'Send e-mail',
    description: 'Opens the e-mail client',
    showSubject: false,
    showBody: false,
);
```

With `showSubject` and `showBody` you can enable a subject and/or body field to pass to the mail client.

## Adding the 'tel' link

If you want to add a "tel:" option in the link picker, you need to add a route like this, in your `AppServiceProvider`:

```php
LinkCollection::addTelephoneLink();
```

## Adding the 'anchor' link

If you want to add an "anchor" option in the link picker, you need to add a route like this, in your `AppServiceProvider`:

```php
LinkCollection::addAnchorLink();
```

Or change some of the default values:
```php
LinkCollection::addAnchorLink(
    routeName: 'anchor',
    group: 'General',
    label: 'Anchor link',
    description: 'Link to achor on current page',
);
```

This will fetch the [Architect](https://github.com/wotzebra/filament-architect) fields from the current page and use them as options for the anchor link. If you want to modify this behavior, you can add a `anchorList` method to your model:

```php
public function anchorList(): array
{
    return [
        'first-section' => 'First section',
        'second-section' => 'Second section',
    ];
}
```

## The Link object

You can pass a callback to the `linkPicker()` function, this callback has one parameter called `$link`, this is a `Wotz\LinkPicker\Link` object. With this object you can configure the link to your needs.

For example, adding a label to make your link more readable in Filament:

```php
use Wotz\LinkPicker\Link;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'show'])
    ->name('home')
    ->linkPicker(fn (Link $link) => $link->label('Homepage'));
```

## The Link object

### Schema

If you use route model binding in your controller, the link picker will automatically build its schema according to the parameters of the route. However, you can still define the schema yourself if you want to. For example:

```php
use Wotz\LinkPicker\Link;
use Illuminate\Support\Facades\Route;

Route::get('{page:slug}', [PageController::class, 'show'])
    ->name('page.show')
    ->linkPicker(fn (Link $link) => $link->schema(fn () => [
            Filament\Forms\Components\Select::make('slug')
                ->options(Page::pluck('name', 'id'))
                ->multiple(),
        ])
    );
```

### Label

You can set a label for your link, this will be shown in the link picker in Filament. For example:

```php
use Wotz\LinkPicker\Link;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'show'])
    ->name('home')
    ->linkPicker(fn (Link $link) => $link->label('Homepage'));
```

### Description

You can set a description for your link, this will be shown in the link picker in Filament. For example:

```php
use Wotz\LinkPicker\Link;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'show'])
    ->name('home')
    ->linkPicker(fn (Link $link) => $link->description('The homepage of the website'));
```

### Group

You can group your links in Filament, this will make it easier to find them. For example:

```php
use Wotz\LinkPicker\Link;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'show'])
    ->name('home')
    ->linkPicker(fn (Link $link) => $link->group('Website'));
```

### Custom description

If you want to replace the `Selected Parameters` description, you can override the default with the `->buildDescriptionUsing()` method. For example:

```php
use Wotz\LinkPicker\Link;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'show'])
    ->name('home')
    ->linkPicker(fn (Link $link) => $link->buildDescriptionUsing(function (array $parameters) {
        return 'a string';
    }));
```

### Building routes

Say for example you have a route like this:

```php
use Illuminate\Support\Facades\Route;

Route::get('blog/{category:slug}/{post:slug}', [PostController::class, 'show'])
    ->name('post.show')
    ->linkPicker();
```

This route has two parameters but since the blogpost is linked to a category, there is no need to select the category in the link picker. You can use the `buildUsing` method to build the route for the link picker. For example:

```php
use Wotz\LinkPicker\Link;
use Illuminate\Support\Facades\Route;

Route::get('blog/{category:slug}/{post:slug}', [PostController::class, 'show'])
    ->name('post.show');
    ->linkPicker(fn (Link $link) => $link
        ->schema(fn () => [
            Filament\Forms\Components\Select::make('post')
                ->options(BlogPost::pluck('title', 'id'))
                ->multiple(),
        ])
        ->buildUsing(function (Link $link) {
            $post = BlogPost::find($link->getParameter('post'));

            return route($link->route, [
                'category' => $post->category,
                'post' => $post,
            ]);
        })
    );
```

## The Filament field

You can add the linkpicker field to your resource like this:

```php
use Wotz\LinkPicker\Filament\LinkPickerInput;

public static function form(Form $form): Form
{
    return $form->schema([
        LinkPickerInput::make('link'),
    ]);
}
```

## Reading the link picker route in the front-end

This package comes with a helper function called `lroute()`, with it you can read your link in the front-end like so:

```html
<a href="{{ lroute($page->route) }}">
    {{ $page->title }}
</a>
```

If you have enabled the route to open in a new tab, the helper will automatically add the `target="_blank"` attribute to the link as well.

## Customizing the route parameters list

### Title field

We will show the `id` in the linkpicker dropdown when choosing a model for a parameter.
But this can be customized in your model, via the static property `$linkPickerTitleField`

```php
public static $linkPickerTitleField = 'working_title';
```

### Customizing parameter query

We will show all models in the parameter select, but this can be customized by adding a static method `linkPickerParameterQuery` on the model.

```php
public static function linkPickerParameterQuery($query): void
{
    $query->where('is_published', true);
}
```
