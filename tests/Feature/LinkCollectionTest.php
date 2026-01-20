<?php

use Wotz\LinkPicker\Link;
use Wotz\LinkPicker\LinkCollection;

beforeEach(function () {
    $this->collection = new LinkCollection;
});

it('can add link', function () {
    $this->collection->addLink(Link::make('route.name'));

    expect($this->collection)
        ->count()->toBe(1)
        ->first()->toBeInstanceOf(Link::class);
});

it('can add group', function () {
    $this->collection->addGroup('Group name', [
        Link::make('route.index', 'Label text'),
        Link::make('route.show', 'Label text'),
    ]);

    expect($this->collection)
        ->count()->toBe(2)
        ->toArray()->each->toBeInstanceOf(Link::class);
});

it('can get routes', function () {
    $this->collection->addLink(Link::make('route.name'));

    expect($this->collection->routes())
        ->count()->toBe(1)
        ->first()->toBeInstanceOf(Link::class);
});

it('can get single named route', function () {
    $this->collection->addLink(Link::make('route.name', 'Label text'));

    expect($this->collection->route('route.name'))
        ->toBeInstanceOf(Link::class)
        ->getLabel()->toBe('Label text')
        ->getRouteName()->toBe('route.name');
});
