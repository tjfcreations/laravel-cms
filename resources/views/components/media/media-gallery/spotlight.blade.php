@props([
    'model'
])

@php
    $media_item = $model->getFirstMedia();
@endphp

<x-fd-cms::media.media-gallery.item :item="$media_item" {{ $attributes }} />