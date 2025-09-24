@props([
    'model', 
    'truncate' => 0, 
    'videoIndicator' => null, 
    'itemSlot' => null, 
    'spotlightSlot' => null,
    'size' => 'md'
])

@php
    $items = $model->media->sortBy('order_column')->values();
@endphp

@foreach ($items as $i => $item)
    @php($slot = $i === 0 ? $spotlightSlot : $itemSlot)      
    <x-fd-cms::media.merged-slot :merge="$slot">
        <x-fd-cms::media.media-gallery.item :item="$item" :videoIndicator="$videoIndicator" :imgClass="$slot->attributes->get('img-class') ?? $slot->attributes->get('imgClass')" :size="$slot->attributes->get('size')" />
    </x-fd-cms::media.merged-slot>
@endforeach
