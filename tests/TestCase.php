<?php

namespace Wotz\LinkPicker\Tests;

use Wotz\LinkPicker\Providers\LinkPickerServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Support\SupportServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LinkPickerServiceProvider::class,
            FilamentServiceProvider::class,
            LivewireServiceProvider::class,
            SupportServiceProvider::class,
        ];
    }
}
