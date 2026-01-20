<?php

namespace Codedor\LinkPicker\Filament;

use Codedor\LinkPicker\Facades\LinkCollection;
use Codedor\LinkPicker\Link;
use Codedor\LocaleCollection\Facades\LocaleCollection;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Optional;
use Illuminate\Support\Reflector;
use Illuminate\Support\Str;
use ReflectionParameter;

class LinkPickerInput extends Field
{
    protected string $view = 'filament-link-picker::filament.link-picker';

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerActions([
            \Filament\Actions\Action::make('link-picker-modal')
                ->label(fn ($state) => $state
                    ? __('filament-link-picker::input.edit link')
                    : __('filament-link-picker::input.select link')
                )
                ->icon(fn ($state) => $state ? 'heroicon-o-pencil' : 'heroicon-o-plus')
                ->color('gray')
                ->iconSize('sm')
                ->fillForm(fn (\Filament\Schemas\Components\Component $component): array => $component->getState() ?? [])
                ->schema(function () {
                    return [
                        Grid::make(1)->schema(function (\Livewire\Component $livewire) {
                            $mountedAction = Arr::last($livewire->mountedActions);
                            $mountedActionIndex = array_key_last($livewire->mountedActions);
                            $schema = $this->getFormSchemaForRoute($mountedAction['data']['route'] ?? null);

                            // since the fields are dynamic we have to fill the state manually,
                            // else validation will fail because property is not in the state
                            data_fill($livewire, "mountedActions.{$mountedActionIndex}.data.parameters", []);
                            $schema->each(function (Field $field) use (&$livewire, $mountedActionIndex) {
                                data_fill(
                                    $livewire,
                                    "mountedActions.{$mountedActionIndex}.data.{$field->statePath}",
                                    null,
                                );
                            });

                            return $schema->toArray();
                        }),
                    ];
                })
                ->action(function (\Filament\Schemas\Components\Utilities\Set $set, array $data, \Filament\Schemas\Components\Component $component) {
                    $set($component->getStatePath(false), $data);
                }),

            \Filament\Actions\Action::make('link-picker-clear')
                ->label(__('filament-link-picker::input.remove link'))
                ->icon('heroicon-o-trash')
                ->iconSize('sm')
                ->color('danger')
                ->action(function (\Filament\Schemas\Components\Utilities\Set $set) {
                    $set($this->getStatePath(false), null);
                }),
        ]);
    }

    public function getRouteDescription()
    {
        $state = $this->getState();

        if (! $state) {
            return null;
        }

        $route = LinkCollection::cleanRoute($state['route']);

        if (! $route) {
            return [];
        }

        if ($route->getBuildDescriptionUsing()) {
            $parameters = Arr::wrap($route->getBuildDescriptionUsing()($state['parameters'] ?? []));
        } else {
            $route->parameters($state['parameters'] ?? []);

            $parameters = $route->getParameters();
            $resolvedRoute = $route->resolveParameters($parameters);

            foreach ($resolvedRoute->parameters ?? [] as $key => $value) {
                if ($value instanceof Model) {
                    $value = $value->{$value::$linkPickerTitleField ?? 'id'};
                    $parameters[$key] = $value;
                }
            }
        }

        return [
            'group' => $route->getGroup(),
            'label' => $route->getLabel(),
            'parameters' => $parameters,
            'newTab' => $state['newTab'] ?? false,
            'custom' => (bool) $route->getBuildDescriptionUsing(),
        ];
    }

    private function getFormSchemaForRoute(?string $selectedRoute): Collection
    {
        $routeField = Select::make('route')
            ->label(__('filament-link-picker::input.route label'))
            ->options(function () {
                return LinkCollection::values()
                    ->unique(fn (Link $link) => $link->getCleanRouteName())
                    ->groupBy(fn (Link $link) => $link->getGroup())
                    ->sortKeys()
                    ->map(fn (Collection $links) => $links->mapWithKeys(fn (Link $link) => [
                        $link->getCleanRouteName() => $link->getLabel(),
                    ]));
            })
            ->required()
            ->live();

        if (! $selectedRoute) {
            return collect([$routeField]);
        }

        $link = LinkCollection::firstByCleanRouteName($selectedRoute);

        if (is_null($link)) {
            return collect([$routeField]);
        }

        $schema = $link->getSchema();

        $routeParameters = $this->routeParameters($link->getRoute());

        // If the schema is empty, we'll check if there are any parameters
        if ($schema->isEmpty()) {
            $schema = $routeParameters
                ->map(function (ReflectionParameter $parameter) {
                    $model = Reflector::getParameterClassName($parameter);

                    return Select::make("parameters.{$parameter->name}")
                        ->label(Str::title($parameter->name))
                        ->required(! $parameter->allowsNull())
                        ->searchable()
                        ->options($model::query()
                            ->when(method_exists($model, 'linkPickerParameterQuery'), fn ($query) => $model::linkPickerParameterQuery($query))
                            ->withoutGlobalScopes()
                            ->pluck(
                                $model::$linkPickerTitleField ?? 'id',
                                (new $model)->getKeyName(),
                            )
                        )
                        ->live();
                });
        }

        if ($anchorData = $link->getWithAnchors()) {
            if (! data_get($anchorData, 'parameter')) {
                $anchorData['parameter'] = $routeParameters->first()->name;
            }

            if (! data_get($anchorData, 'model')) {
                $anchorData['model'] = Reflector::getParameterClassName($routeParameters->first());
            }

            $schema->add(
                Select::make('parameters.anchor')
                    ->hidden(fn (\Filament\Schemas\Components\Utilities\Get $get) => ! $get("parameters.{$anchorData['parameter']}"))
                    ->options(function (\Filament\Schemas\Components\Utilities\Get $get) use ($anchorData) {
                        /**
                         * @var Model $record
                         */
                        $record = $anchorData['model']::find($get("parameters.{$anchorData['parameter']}"));

                        if (method_exists($record, 'isTranslatableAttribute') && $record->isTranslatableAttribute($anchorData['field'])) {
                            $locale = referer_locale() ?? (class_exists(LocaleCollection::class)
                                ? LocaleCollection::first()->locale()
                                : 'en');

                            return optional($record->getTranslation($anchorData['field'], $locale))->anchorList();
                        }

                        return $record->{$anchorData['field']}->anchorList();
                    })
            );
        }

        return $schema
            ->prepend($routeField)
            ->add(
                Checkbox::make('newTab')
                    ->label(__('filament-link-picker::input.new tab label'))
            );
    }

    protected function routeParameters(Optional|Route $route): Collection
    {
        if ($route instanceof Optional) {
            return collect();
        }

        return collect($route->signatureParameters())
            ->filter(function (ReflectionParameter $parameter) {
                $className = Reflector::getParameterClassName($parameter);

                return $parameter->getType()
                    && class_exists($className)
                    && is_subclass_of($className, Model::class);
            });
    }
}
