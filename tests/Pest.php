<?php

use Illuminate\Support\Facades\Route;
use Mockery\MockInterface;
use Wotz\LinkPicker\PackageChecker;
use Wotz\LinkPicker\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function registerRoute(): void
{
    Route::get('route/{parameter}', fn () => 'route.name')
        ->name('route.name')
        ->linkPicker();

    app('router')->getRoutes()->refreshNameLookups();
}

function registerRouteWithoutParameters(): void
{
    Route::get('route', fn () => 'route.without-parameters')
        ->name('route.without-parameters')
        ->linkPicker();

    app('router')->getRoutes()->refreshNameLookups();
}

function mockPackageChecker(): void
{
    test()->instance(
        PackageChecker::class,
        Mockery::mock(Translator::class, function (MockInterface $mock) {
            $mock->shouldReceive('localeCollectionClassExists')->andReturn(false);
            $mock->shouldReceive('translateRouteFunctionExists')->andReturn(false);
        })
    );
}
