<?php

namespace Wotz\LinkPicker;

class PackageChecker
{
    public function localeCollectionClassExists(): bool
    {
        return class_exists(\Wotz\LocaleCollection\Facades\LocaleCollection::class);
    }

    public function translateRouteFunctionExists(): bool
    {
        return function_exists('translate_route');
    }
}
