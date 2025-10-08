class MediaModalContent {
    constructor() {
    }

    getElementSize(element) {
        const windowRatio = window.innerWidth / window.innerHeight;
        const elementRatio = this.getElementRatio(element);

        if(windowRatio > elementRatio) {
              // If viewport is wider than element, set height to max
            return { width: 'auto', height: '90vh' };
        } else {
            // If viewport is taller than element, set width to max
            return { width: '90vw', height: 'auto' };
        }
    }

    getElementRatio(element) {
        switch(element.tagName.toLowerCase()) {
            case 'img':
                return element.naturalWidth / element.naturalHeight;
            case 'video':
                return element.videoWidth / element.videoHeight;
            default:
                return parseInt(element.width) / parseInt(element.height);

        }
    }
}