<?php

use Wotz\LinkPicker\Link;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Collection;

it('can set and get route name', function () {
    $link = Link::make('route.name')->routeName('route.new-name');

    expect($link)->getRouteName()->toBe('route.new-name');
});

it('can set and get label', function () {
    $link = Link::make('route.name', 'Old label')->label('Label text');

    expect($link)->getLabel()->toBe('Label text');
});

it('can set and get description', function () {
    $link = Link::make('route.name')->description('Description text');

    expect($link)->getDescription()->toBe('Description text');
});

it('can set and get schema', function () {
    $link = Link::make('route.name');

    $link->schema(fn () => TextInput::make('Text input'));

    expect($link)
        ->getSchema()->toBeInstanceOf(Collection::class)
        ->getSchema()->first()->toBeInstanceOf(TextInput::class);
});

it('can set and get group', function () {
    $link = Link::make('route.name')->group('Group text');

    expect($link)->getGroup()->toBe('Group text');
});

it('can set and get parameters', function () {
    $link = Link::make('route.name')->parameters(['key' => 'value']);

    expect($link)->getParameters()->toBe(['key' => 'value']);
});

it('can set and get a single parameter', function () {
    $link = Link::make('route.name')->parameters(['key' => 'value']);

    expect($link)->getParameter('key')->toBe('value');
});

it('can set and get build using callback', function () {
    $link = Link::make('route.name')->buildUsing(fn () => 'build using');

    expect($link)
        ->build()->toBe('build using');
});

it('can build without callback and parameters', function () {
    registerRoute();
    mockPackageChecker();

    $parameters = ['parameter' => 'test'];

    expect(Link::make('route.name'))
        ->buildUsing->toBeNull()
        ->build($parameters)->toBe(route('route.name', $parameters));
});
