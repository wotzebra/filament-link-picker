<?php

namespace Wotz\LinkPicker;

use Wotz\LocaleCollection\Facades\LocaleCollection;

class PackageChecker
{
    public function localeCollectionClassExists(): bool
    {
        return class_exists(LocaleCollection::class);
    }

    public function translateRouteFunctionExists(): bool
    {
        return function_exists('translate_route');
    }
}
