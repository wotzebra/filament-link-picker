<?php

use Wotz\LinkPicker\Link;

beforeEach(function () {
    registerRoute();

    $this->parameters = ['parameter' => 'value'];
    $this->routeToCheck = route('route.name', $this->parameters);
});

it('can use lroute helper with a link object', function () {
    mockPackageChecker();
    $link = Link::make('route.name')->parameters($this->parameters);
    $route = lroute($link);

    expect($route)->toBe($this->routeToCheck);
});

it('can use lroute helper with an array', function () {
    mockPackageChecker();

    $route = (string) lroute([
        'route' => 'route.name',
        'parameters' => $this->parameters,
    ]);

    expect($route)->toBe($this->routeToCheck);
});

it('returns null when a null is given', function () {
    expect(lroute(null))->toBeNull();
});
