@props([
    'model',
    'items' => null,
    'modalBackButton' => null,
    'modalNextButton' => null,
    'modalMeta' => null,
    'item' => null,
    'spotlight' => null
])

<x-fd-cms::media.media-gallery._context>
    <x-fd-cms::media.media-gallery._modal 
        :back-button-slot="$modalBackButton"
        :next-button-slot="$modalNextButton"
        :meta-slot="$modalMeta" />
    
    <div {{ $attributes }}>
        <x-fd-cms::media.media-gallery.items
            :item-slot="$item"
            :spotlight-slot="$spotlight"
            :model="$model" />
    </div>
</x-fd-cms::media.media-gallery._context>