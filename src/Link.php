<?php

namespace Codedor\LinkPicker;

use Closure;
use Filament\Forms\Components\Field;
use Illuminate\Routing\ImplicitRouteBinding;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class Link
{
    protected ?string $description = null;

    protected ?string $group = null;

    protected ?Closure $buildUsing = null;

    protected ?Closure $buildDescriptionUsing = null;

    protected ?Closure $schema = null;

    protected array $parameters = [];

    protected ?array $withAnchors = null;

    public function __construct(
        protected string $routeName,
        protected ?string $label = null,
    ) {}

    public static function make(string $routeName, ?string $label = null): self
    {
        return new self($routeName, $label);
    }

    public function routeName(string $routeName)
    {
        $this->routeName = $routeName;

        return $this;
    }

    public function getRouteName(): string
    {
        return $this->routeName;
    }

    public function label(string $label)
    {
        $this->label = $label;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label ?? Str::of($this->getCleanRouteName())->after('.')->title();
    }

    public function description(string $description)
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function schema(Closure $schema)
    {
        $this->schema = $schema;

        return $this;
    }

    public function getSchema(): Collection
    {
        $schema = Collection::wrap(
            is_null($this->schema) ? [] : call_user_func($this->schema)
        );

        return $schema->map(fn (Field $field) => $field->statePath(
            "parameters.{$field->getName()}"
        ));
    }

    public function withAnchors(string $field = 'body', ?string $model = null, ?string $parameter = null)
    {
        $this->withAnchors = [
            'field' => $field,
            'model' => $model,
            'parameter' => $parameter,
        ];

        return $this;
    }

    public function getWithAnchors(): ?array
    {
        return $this->withAnchors;
    }

    public function group(string $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getGroup(): string
    {
        return $this->group ?? Str::of($this->getCleanRouteName())->before('.')->replace('-', ' ')->title();
    }

    public function parameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getParameter(string $key): mixed
    {
        return $this->getParameters()[$key] ?? null;
    }

    public function buildUsing(Closure $closure)
    {
        $this->buildUsing = $closure;

        return $this;
    }

    public function buildDescriptionUsing(Closure $closure)
    {
        $this->buildDescriptionUsing = $closure;

        return $this;
    }

    public function getBuildDescriptionUsing(): ?Closure
    {
        return $this->buildDescriptionUsing;
    }

    public function getCleanRouteName()
    {
        $route = $this->getRoute();

        if (app(PackageChecker::class)->localeCollectionClassExists() && ($route->wheres['translatable_prefix'] ?? false)) {
            return Str::after($this->routeName, $route->wheres['translatable_prefix'] . '.');
        }

        return $this->routeName;
    }

    public function getRoute()
    {
        $route = Route::getRoutes()->getByName($this->routeName);

        return $route ? clone $route : optional();
    }

    public function build(?array $parameters = null): ?string
    {
        $parameters ??= $this->getParameters();

        if ($this->buildUsing) {
            return call_user_func($this->buildUsing, $this->parameters($parameters));
        }

        $anchor = isset($parameters['anchor']) ? "#{$parameters['anchor']}" : null;
        $route = $this->resolveParameters($parameters);

        // If route binding fails
        if (! $route) {
            return null;
        }

        if (app(PackageChecker::class)->translateRouteFunctionExists()) {
            $route = translate_route($this->getCleanRouteName(), null, $route->parameters);

            // If the route cannot be found as a translated route, we'll try to find a normal route
            if (! is_null($route) && $route !== '#') {
                return "{$route}{$anchor}";
            }
        }

        return route($this->routeName, $parameters) . $anchor;
    }

    public function resolveParameters(array $parameters)
    {
        $route = $this->getRoute();

        if (isset($parameters['anchor'])) {
            unset($parameters['anchor']);
        }
        $route->parameters = $parameters;
        $bindings = $route->bindingFields();

        try {
            $route->setBindingFields([]);

            ImplicitRouteBinding::resolveForRoute(app(), $route);

            $route->setBindingFields($bindings);
        } catch (\Throwable $th) {
            // report($th);

            return null;
        }

        return $route;
    }
}
