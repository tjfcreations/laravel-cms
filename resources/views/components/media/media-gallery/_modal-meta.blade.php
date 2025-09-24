@props([
    'meta' => null
])

<x-media-gallery::merged-slot 
    :merge="$meta"
    x-data="{ meta: {} }"
    x-effect="if(modal.index >= 0 && modal.getCurrentItem()) meta = modal.getCurrentItem().data.meta ?? {};"
    x-show="Object.values(meta).length > 0"
    class="absolute bottom-[calc(5vh+0.5rem)] text-center md:text-left md:left-[calc(min(5vw,_10rem))] text-white z-[100] bg-black/40 p-2 rounded-md">
    <span x-text="meta.description" class="block"></span>
    <span x-text="[meta.author, meta.date].filter(v => typeof v === 'string').join(', ')" class="block text-zinc-300"></span>
</x-media-gallery::merged-slot>