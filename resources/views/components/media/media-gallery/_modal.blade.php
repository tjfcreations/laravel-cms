@props([
    'backButtonSlot' => null,
    'nextButtonSlot' => null,
    'metaSlot' => null
])

<div 
    id="media-gallery-modal"
    class="fixed top-0 right-0 bottom-0 left-0 flex items-center justify-center select-none z-50 transition ease-in-out duration-300" 
    role="dialog"
    aria-modal="true"
    x-bind:aria-hidden="modal.isOpen ? 'false' : 'true'"
    x-on:keydown.left.window="modal.isOpen && modal.navigate(-1)" 
    x-on:keydown.right.window="modal.isOpen && modal.navigate(1)"
    x-on:keydown.escape.window="modal.isOpen && modal.close()"
    x-show="modal.isOpen"
    x-transition:enter-start="translate-y-5 opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="translate-y-5 opacity-0"
    x-bind:style="`--modal-direction: ${modal.direction};`"
    style="display: none;">

    {{-- back button --}}
    <x-media-gallery::media-gallery._modal-button action="back" :button="$backButtonSlot" />

    {{-- next button --}}
    <x-media-gallery::media-gallery._modal-button action="next" :button="$nextButtonSlot" />

    {{-- image --}}
    <template x-for="item in modal.getItems()">
        <template x-if="item.getDistanceFromCurrentItem() <= 1">
            <template x-if="item.element.tag === 'img'">
                <img 
                    x-data="{ 
                        updateElementSize() {
                            const elementSize = modal.content.getElementSize($el, modal);
                            $el.style.height = elementSize.height;
                            $el.style.width = elementSize.width;
                        },
                        isVisible() { 
                            this.updateElementSize();
                            return item === modal.getCurrentItem();
                        },
                        getSrc() {
                            this.updateElementSize();
                            return item.element.attributes.src;
                        }
                    }"

                    x-bind:class="`absolute rounded-lg overflow-hidden object-cover ${isVisible() ? 'z-50' : 'z-40'} ${modal.direction === 0 ? '' : 'transition ease-in-out duration-150'}`"

                    x-bind:src="getSrc()" 
                    x-bind:alt="item.element.attributes.alt"
                    x-on:resize.window="updateElementSize()"
                    x-show="isVisible()"

                    x-transition:enter-start="opacity-0 translate-x-[calc(var(--modal-direction)_*_20px)]"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0" />
            </template>
        </template>
    </template>
    

    {{-- backdrop --}}
    <div 
        class="bg-black/80 absolute top-0 right-0 bottom-0 left-0 transition ease-in-out duration-300" 
        x-on:click="modal.close()" 
        x-show="modal.isOpen" 
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"></div>

    {{-- metadata --}}
    <x-media-gallery::media-gallery._modal-meta :meta="$metaSlot" />
</div>