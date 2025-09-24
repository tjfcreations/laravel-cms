@props([
    'merge' => null,
    'tag' => 'div'
])

@php
    use Illuminate\View\ComponentSlot;

    $merge = $merge instanceof ComponentSlot ? $merge : new ComponentSlot();
@endphp

<{{ $tag }} 
    {{ $merge->attributes->merge($attributes->toArray())->except('class') }}
    {{ $merge->attributes->has('class') ? $merge->attributes->only('class') : $attributes->only('class') }}>
    @if($merge->hasActualContent())
        {{ $merge }}
    @else
        {{ $slot }}
    @endif
</{{ $tag }}>