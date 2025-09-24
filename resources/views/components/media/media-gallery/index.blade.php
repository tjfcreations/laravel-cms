@props([
    'model',
    'items' => null,
    'modalBackButton' => null,
    'modalNextButton' => null,
    'modalMeta' => null,
    'item' => null,
    'spotlight' => null
])

<x-media-gallery::media-gallery._context>
    <x-media-gallery::media-gallery._modal 
        :back-button-slot="$modalBackButton"
        :next-button-slot="$modalNextButton"
        :meta-slot="$modalMeta" />
    
    <div {{ $attributes }}>
        <x-media-gallery::media-gallery.items
            :item-slot="$item"
            :spotlight-slot="$spotlight"
            :model="$model" />
    </div>
</x-media-gallery::media-gallery._context>