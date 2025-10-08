class MediaModalItem {
    modal;
    data;
    thumbnailElements;

    element;

    constructor(modal, data) {
        this.modal = modal;
        this.data = data;
        this.thumbnailElements = [];

        this.element = {};
    }
    
    getDistanceFromCurrentItem() {
        return Math.abs(this.modal.index - this.modal.getIndexForItem(this));
    }

    isImage() {
        return true;
    }

    show() {
        const thumbnail = this.getFirstThumbnailElement();
        this.showElement('img', { src: thumbnail.src, alt: thumbnail.alt });

        if(this.isImage()) {
            this.loadImage(img => {
                this.showElement('img', { src: img.currentSrc ?? thumbnail.src, alt: thumbnail.alt });
            })
        }
    }
    
    loadImage(callback) {
        const img = document.createElement('img');
        img.onload = () => {
            if(this !== this.modal.getCurrentItem()) return;
            callback(img);
        }

        img.src = this.getFirstThumbnailElement().src;

        const ratio = this.modal.content.getElementRatio(img);
        img.src = this.getBestSource(ratio, window.innerWidth, window.innerHeight).url;
    }

    showElement(tag, attributes) {
        this.element = { tag, attributes };
    }

    getBestSource(ratio, containerWidth, containerHeight) {
        const dimension = ratio > 1 ? containerWidth : containerHeight;
        const sourceMinDimension = dimension * 0.9;
        console.log({ sourceMinDimension });

        const sources = Object.values(this.data.sources).sort((a, b) => a.width > b.width ? 1 : -1);
        const bestSource = sources.find(source => source.width >= sourceMinDimension && source.height >= sourceMinDimension);

        return bestSource ?? sources[sources.length-1];
    }

    getImageSizes() {
        return Object.values(this.data.sources).map(source => `(max-width: ${Math.round(source.width, 0.9)}px) ${source.width}px`).join(', ') + ', 1440px';
    }

    getImageSrcSet() {
        return Object.values(this.data.sources).map(source => `${source.url} ${source.width}w`).join(', ');
    }

    getThumbnailElements() {
        return this.thumbnailElements;
    }

    getFirstThumbnailElement() {
        return this.thumbnailElements[0];
    }

    addThumbnailElement(element) {
        return this.thumbnailElements.push(element);
    }
}