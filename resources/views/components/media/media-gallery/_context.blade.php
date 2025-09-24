<script src="{{ asset('vendor/tjall/laravel-mediagallery/js/modal/MediaModal.js') }}" defer></script>
<script src="{{ asset('vendor/tjall/laravel-mediagallery/js/modal/MediaModalItem.js') }}" defer></script>
<script src="{{ asset('vendor/tjall/laravel-mediagallery/js/modal/MediaModalContent.js') }}" defer></script>

<div x-data="{ modal: new MediaModal() }">
    {{ $slot }}
</div>