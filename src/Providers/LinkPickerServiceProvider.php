<?php

namespace Wotz\LinkPicker\Providers;

use Exception;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wotz\LinkPicker\Facades\LinkCollection as FacadesLinkCollection;
use Wotz\LinkPicker\Link;
use Wotz\LinkPicker\LinkCollection;

class LinkPickerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-link-picker')
            ->setBasePath(__DIR__ . '/../')
            ->hasViews('filament-link-picker')
            ->hasTranslations();
    }

    public function registeringPackage()
    {
        $this->app->singleton(LinkCollection::class, function () {
            return new LinkCollection;
        });
    }

    public function bootingPackage()
    {
        Route::macro('linkPicker', function (?callable $callback = null) {
            /** @var \Illuminate\Routing\Route $this */
            $link = new Link($this->getName());

            if (Str::endsWith($this->getName(), '.')) {
                throw new Exception(
                    "You'll need to define a ->name() for the route [{$this->uri}] before you can call ->linkPicker()"
                );
            }

            FacadesLinkCollection::add(
                $callback ? call_user_func($callback, $link) : $link
            );

            return $this;
        });
    }
}
