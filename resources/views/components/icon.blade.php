{{-- Inline SVG icon; see App\View\Components\Icon --}}
{!! str_replace('<svg', '<svg '.$attributes->merge(['aria-hidden' => 'true', 'focusable' => 'false'])->toHtml(), $svg()) !!}
