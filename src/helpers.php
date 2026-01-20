<?php

use Illuminate\Support\HtmlString;
use Wotz\LinkPicker\Facades\LinkCollection;
use Wotz\LinkPicker\Link;

if (! function_exists('lroute')) {
    function lroute(null|string|array|Link $link, ?array $parameters = null, bool $withTarget = true): HtmlString|string|null
    {
        if (is_string($link)) {
            $link = json_decode($link, true);
        }

        if (blank($link)) {
            return null;
        }

        if ($link instanceof Link) {
            return $link->build($parameters);
        }

        $url = LinkCollection::firstByCleanRouteName($link['route'])
            ?->build($link['parameters'] ?? []);

        if (! $url) {
            return null;
        }

        if ($withTarget && ($link['newTab'] ?? false)) {
            $slash = is_livewire_route(request()) ? '\\' : '';
            $url .= $slash . '" target=' . $slash . '"_blank';
        }

        return new HtmlString($url);
    }
}

if (! function_exists('parse_link_picker_json')) {
    function parse_link_picker_json(string $content): string
    {
        return preg_replace_callback(
            '/#link-picker=\[\[([^"]*)]]/',
            function ($matches) {
                $data = html_entity_decode($matches[1]);

                $json = json_decode($data, true);

                if (! $json) {
                    return '';
                }

                return lroute($json);
            },
            $content
        );
    }
}

if (! function_exists('referer_locale')) {
    function referer_locale(): ?string
    {
        $referer = request()->headers->get('referer');

        preg_match('/locale=-(.*)-tab/', $referer, $matches);

        return $matches[1] ?? null;
    }
}
