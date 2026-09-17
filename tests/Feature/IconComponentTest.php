<?php

use App\View\Components\Icon;
use Illuminate\Support\Facades\Blade;

it('renders a vendored lucide icon inline', function () {
    $html = Blade::render('<x-icon name="cloud-rain" class="size-6 text-sky-500" />');

    expect($html)
        ->toContain('<svg')
        ->toContain('class="size-6 text-sky-500"')
        ->toContain('stroke="currentColor"')
        ->toContain('aria-hidden="true"')
        ->not->toContain('width="24"')
        ->not->toContain('lucide lucide-cloud-rain');
});

it('fails loudly for an unknown icon', function () {
    (new Icon('not-a-real-icon'))->svg();
})->throws(InvalidArgumentException::class, 'Icon [not-a-real-icon] not found in resources/icons/lucide.');
