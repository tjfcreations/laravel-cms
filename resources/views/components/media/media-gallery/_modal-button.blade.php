@props([
    'action' => '',
    'button' => null
])

@php
    use Illuminate\View\ComponentSlot;

    $offset = $action == 'back' ? - 1 : 1;
    $button = $button instanceof ComponentSlot ? $button : new ComponentSlot();
@endphp

<div 
    {{ $attributes->class([
        'absolute top-1/2 -translate-y-1/2 z-[100]',
        'left-[calc(min(5vw,_10rem))]' => $action === 'back',
        'right-[calc(min(5vw,_10rem))]' => $action === 'next'
    ]) }}>
        <button {{ $button->attributes->class('bg-primary-600 text-white p-3 rounded-full group') }} 
            x-on:click="modal.navigate({{ $offset }})" 
            x-show="modal.hasIndex(modal.index + {{ $offset }})"
            x-transition.opacity=""
            x-tranisiton.duration.500ms="">
            @if($button->hasActualContent())
                {{ $button }}
            @else
                @if($action === 'back')
                    <svg class="w-6 h-6 group-hover:-translate-x-1 transition duration-150 ease-out" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><!--! Font Awesome Pro 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l192 192c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L77.3 256 246.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-192 192z"/></svg>
                @else
                    <svg class="w-6 h-6 group-hover:translate-x-1 transition duration-150 ease-out" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><!--! Font Awesome Pro 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M310.6 233.4c12.5 12.5 12.5 32.8 0 45.3l-192 192c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L242.7 256 73.4 86.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l192 192z"/></svg>
                @endif
            @endif
        </button>
</div>