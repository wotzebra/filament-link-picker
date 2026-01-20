<?php

namespace Wotz\LinkPicker\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Wotz\LinkPicker\LinkCollection routes()
 * @method static null | \Wotz\LinkPicker\Link route(string $routeName)
 * @method static null | \Wotz\LinkPicker\Link cleanRoute(string $routeName)
 * @method static \Wotz\LinkPicker\LinkCollection addLink(\Wotz\LinkPicker\Link $link)
 * @method static \Wotz\LinkPicker\LinkCollection addGroup(string $group, iterable $links)
 * @method static \Wotz\LinkPicker\Link|null firstByCleanRouteName(string $routeName)
 *
 * @see \Wotz\LinkPicker\LinkCollection
 */
class LinkCollection extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Wotz\LinkPicker\LinkCollection::class;
    }
}
