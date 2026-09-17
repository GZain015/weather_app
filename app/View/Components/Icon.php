<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use InvalidArgumentException;

/**
 * Renders a vendored Lucide icon as inline SVG.
 *
 * Icons live in `resources/icons/lucide` and are never fetched from a CDN.
 * Size and color come from the caller's utility classes, e.g.
 * `<x-icon name="cloud-rain" class="size-6 text-sky-500" />`.
 */
class Icon extends Component
{
    /** @var array<string, string> */
    protected static array $cache = [];

    public function __construct(public string $name) {}

    /**
     * The icon's SVG markup, stripped of the fixed width/height and the
     * upstream `class` attribute so Tailwind utilities win.
     */
    public function svg(): string
    {
        if (isset(static::$cache[$this->name])) {
            return static::$cache[$this->name];
        }

        $path = resource_path("icons/lucide/{$this->name}.svg");

        if (! is_file($path)) {
            throw new InvalidArgumentException("Icon [{$this->name}] not found in resources/icons/lucide.");
        }

        $svg = preg_replace(
            ['/\s(?:width|height)="[^"]*"/', '/\sclass="[^"]*"/', '/<!--.*?-->\s*/s'],
            '',
            (string) file_get_contents($path)
        );

        return static::$cache[$this->name] = trim((string) $svg);
    }

    public function render(): View
    {
        return view('components.icon');
    }
}
