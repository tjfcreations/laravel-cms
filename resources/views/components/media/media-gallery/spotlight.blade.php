@props([
    'model'
])

@php
    $media_item = $model->getFirstMedia();
@endphp

<x-media-gallery::media-gallery.item :item="$media_item" {{ $attributes }} />