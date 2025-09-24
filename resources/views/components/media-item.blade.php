@props([
    'item' => null,
    'class' => '',
    'size' => null,
    'thumbnail' => false
])

@php
    if(isset($item)) {
        $url = $item->getUrl(isset($size) ? $size : 'md');
    }
@endphp

<img 
    {{ $attributes->class(['object-cover', $class]) }}
    @if(isset($url))
        src="{{ $item->getUrl(isset($size) ? $size : 'md') }}" 
        alt="{{ $item->getDescription() }}"
    @else
        src="{{ config('media-gallery.fallback_url') }}"
        alt=""
    @endif
    height="1920" 
    width="1920"
    loading="lazy"  />