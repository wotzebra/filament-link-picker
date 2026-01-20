<?php

use Wotz\LinkPicker\Filament\LinkPickerInput;
use Wotz\LinkPicker\Tests\Fixtures\Forms\Livewire;
use Wotz\LinkPicker\Tests\Fixtures\Models\TestModel;
use Filament\Schemas\Schema;

beforeEach(function () {
    $this->field = LinkPickerInput::make('link')
        ->container(Schema::make(Livewire::make()))
        ->model(TestModel::class);
});

it('can get the current state', function () {
    registerRoute();

    $this->field->state([
        'route' => 'route.name',
        'parameters' => [],
        'newTab' => false,
    ]);

    expect($this->field)
        ->getRouteDescription()->toBe([
            'group' => 'Route',
            'label' => 'Name',
            'parameters' => [],
            'newTab' => false,
            'custom' => false,
        ]);
});
