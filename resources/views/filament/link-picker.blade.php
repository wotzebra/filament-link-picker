<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div class="flex gap-3 items-center">
        {{ $getAction('link-picker-modal') }}

        @if ($routeDescription = $getRouteDescription())
            {{ $getAction('link-picker-clear') }}
            <ul class="bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition rounded text-sm px-2 py-1">
                <li class="break-all">
                    <strong>
                        {{ __('filament-link-picker::input.selected link') }}:
                    </strong>

                    {{ $routeDescription['group'] ? $routeDescription['group'] . ' >' : '' }} {{ $routeDescription['label'] ?? '' }}
                </li>

                @if(! empty($routeDescription['parameters']))
                    <li class="break-all">
                        @if ($routeDescription['custom'] ?? false)
                            {!! $routeDescription['parameters'][0] ?? null !!}
                        @else
                            <strong>
                                {{ __('filament-link-picker::input.selected parameters') }}:
                            </strong>

                            <ul>
                                @foreach ($routeDescription['parameters'] as $name => $value)
                                    @if (filled($value))
                                        <li>
                                            {{ ucfirst($name) }}: {{ $value }}
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endif

                @if ($routeDescription['newTab'] ?? false)
                    <li class="break-all">
                        <strong>
                            {{ __('filament-link-picker::input.selected open in new tab') }}
                        </strong>
                    </li>
                @endif
            </ul>
        @endif
    </div>
</x-dynamic-component>
