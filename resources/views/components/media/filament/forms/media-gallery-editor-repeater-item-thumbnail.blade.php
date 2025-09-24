@props([
    'component',
    'conversion',
    'item' => null,
    'progress' => false
])

@php
    use Filament\Filament;

    $item ??= $component->getContainer()->getRawState();

    $is_upload = isset($item['upload_id']);
    $alpine_store = $is_upload ? "\$store['uploads'][{$item['upload_id']}]" : '{}';

    $colors = filament()->getPanel()->getColors();
@endphp

<div style="position: relative; overflow: hidden;" class="rounded-md">
    <img 
        alt=""  
        @if($is_upload)
            x-bind:src="{{ $alpine_store }}.dataUrl"
        @elseif(isset($item['media_item']))
            src="{{ $item['media_item']->getUrl($conversion) }}"
        @endif

        x-bind:style="'width: 100%; object-fit: cover; aspect-ratio: 1 / 1; transition: 300ms filter ease-out;' + ({{ $alpine_store }}.isUploading ? 'filter: blur(8px) brightness(0.75);' : '')"
        />

    @if($is_upload)
        @if($progress)
            <div 
                x-bind:style="'position: absolute; z-index: 10; border-radius: 99px; height: 0.375rem; bottom: 0.75rem; left: 0.75rem; right: 0.75rem; overflow: hidden; background-color: rgba(255, 255, 255, 0.2); transition: 300ms opacity ease-out;' + ({{ $alpine_store }}.isUploading ? '' : 'opacity: 0;')">
                <div x-bind:style="'background: rgba(255, 255, 255, 1); border-radius: 99px; height: 100%; width: '+{{ $alpine_store }}.progress+'%;'"></div>
            </div>
        @endif

        <x-filament::loading-indicator 
            x-bind:style="'position: absolute; width: 3rem; top: 0; right: 0; bottom: 0; left: 0; margin: auto; z-index: 10; transition: 300ms opacity ease-out;' + ({{ $alpine_store }}.isUploading ? '' : 'opacity: 0;')" />
    @endif
</div>