@props([
    'item' => null,
    'element' => 'span',
])

@php
    $url = $item->url();

    if (!empty($url)) {
        $element = 'a';
        $attributes = $attributes->merge(['href' => $url]);
    }
@endphp

<{{ $element }} {{ $attributes }}>
    @if (isset($slot) && $slot->isNotEmpty())
        {{ $slot }}
    @else
        {{ $item->label() }}
    @endif
    </{{ $element }}>
