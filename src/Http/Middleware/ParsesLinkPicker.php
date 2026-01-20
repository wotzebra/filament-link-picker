<?php

namespace Wotz\LinkPicker\Http\Middleware;

use Closure;

class ParsesLinkPicker
{
    public function handle($request, Closure $next)
    {
        if (is_filament_livewire_route($request)) {
            return $next($request);
        }

        $response = $next($request);

        if ($response->getContent()) {
            $response->setContent(parse_link_picker_json($response->getContent()));
        }

        return $response;
    }
}
