@php
    $accept = implode(', ', $getAcceptedFileTypes());
    $statePath = $getStatePath();

    $repeaterStatePath = str_replace('-file-upload', '-repeater', $statePath);
@endphp

<div x-data="{
    isDropping: false,
    isUploading: false,
    uploadId: 0,
    progress: 0,
    init() {
        $store['uploads'] = {};
    },
    handleFileSelect(event) {
        if (!event.target.files.length) return;
        this.uploadFiles(event.target.files);
    },
    handleFileDrop(event) {
        if (!event.dataTransfer.files.length) return;
        this.uploadFiles(event.dataTransfer.files);
    },
    uploadFiles(files) {
        const uploadIds = Array.from(files).map(file => {
            return this.registerUpload({ file, dataUrl: URL.createObjectURL(file) });
        });

        this.mountAction('onUploadStart', { uploadIds }).then(() => {
            uploadIds.forEach(uploadId => {
                const upload = $store['uploads'][uploadId];

                $wire.upload('{{ $statePath }}#upload-'+uploadId, upload.file,
                    success => {
                        this.mountAction('onUploadSuccess', { uploadIds: [ uploadId] });
                        this.updateUpload(uploadId, { isUploading: false });
                    },
                    error => { //an error occured
                        this.dispatchMediaUploadEvent('error', [ uploadId ]);
                        this.updateUpload(uploadId, { isUploading: false });
                    },
                    event => { //upload progress was made
                        this.updateUpload(uploadId, { progress: event.detail.progress });
                    }
                )
            })
        })

    },
    registerUpload(data) {
        const uploadId = this.uploadId++;
        $store['uploads'][uploadId] = { ...data, isUploading: true, progress: 0 };

        return uploadId;
    },
    updateUpload(uploadId, data) {
        $store['uploads'][uploadId] = { ...$store['uploads'][uploadId], ...data };
    },
    mountAction(name, arguments) {
        return $wire.mountFormComponentAction('{{ $repeaterStatePath }}', name, arguments);
    }
}" class="w-full">
    <div class="flex flex-col items-center justify-center w-full relative" x-on:drop="isDroppingFile = false"
        x-on:drop.prevent="handleFileDrop($event)" x-on:dragover.prevent="isDroppingFile = true"
        x-on:dragleave.prevent="isDroppingFile = false">
        <x-filament::section class="w-full">
            <div class="absolute top-0 bottom-0 left-0 right-0 z-30 flex items-center justify-center bg-blue-500 opacity-90"
                x-show="isDropping">
                <span class="text-3xl text-white">Release file to upload!</span>
            </div>
            <label class="flex flex-col items-center justify-center w-full cursor-pointer" for="file-upload"
                id="file-upload-label">
                <p class="text-lg max-w-none">Sleep hier je bestanden naartoe</p>
                <p class="max-w-none">of</p>
                <x-filament::button tag="div" class="cursor-pointer mt-1">Bestanden
                    selecteren</x-filament::button>
            </label>
            <input type="file" id="file-upload" multiple x-on:change="handleFileSelect" class="hidden"
                accept="{{ $accept }}" />
        </x-filament::section>
    </div>
</div>
