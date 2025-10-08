class MediaModal {
    items;
    order;
    index;
    direction;

    isOpen;

    constructor() {
        this.content = new MediaModalContent();

        this.items = {};
        this.order = [];
        this.index = -1;
        this.isOpen = false;
    }

    registerItem(data, thumbnailElement) {
        if(!this.items[data.uuid]) {
            const item = new MediaModalItem(this, data, thumbnailElement);
            this.items[data.uuid] = item;
            this.order.push(data.uuid);
        }

        this.getItem(data.uuid).addThumbnailElement(thumbnailElement);
    }

    navigate(offsetIndex = 1) {
        const newIndex = this.index + offsetIndex;
        if(!this.hasIndex(newIndex)) return false;

        this.direction = (newIndex > this.index ? 1 : -1);
        this.open(this.getItemByIndex(newIndex));
    }

    open(item) {
        this.isOpen = true;
        this.index = this.getIndexForItem(item);

        item.show();
    }

    openByUuid(uuid) {
        return this.open(this.getItem(uuid));
    }

    getCurrentItem() {
        return this.getItemByIndex(this.index);
    }

    getItem(uuid) {
        return this.items[uuid];
    }

    getIndexForItem(item) {
        return this.order.indexOf(item.data.uuid);
    }

    getItemByIndex(index) {
        return this.getItem(this.order[index]);
    }

    hasIndex(index) {
        return typeof this.order[index] !== 'undefined';
    }

    getItems() {
        return Object.values(this.items);
    }

    getVisibleItems() {
        return this.getItems();
    }

    setOpen(isOpen = true) {
        this.isOpen = !!isOpen;
    }

    close() {
        this.direction = 0;
        return this.setOpen(false);
    }
}