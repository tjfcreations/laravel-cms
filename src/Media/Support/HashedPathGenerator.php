<?php
    namespace FeenstraDigital\LaravelCMS\Media\Support;

    use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator as SpatiePathGeneratorInterface;
    use Spatie\MediaLibrary\MediaCollections\Models\Media;

    class HashedPathGenerator implements SpatiePathGeneratorInterface {
        public function getPath(Media $media): string {
            return md5($media->id . $media->file_name) . '/';
        }

        public function getPathForConversions(Media $media): string {
            return $this->getPath($media) . 'resized/';
        }

        public function getPathForResponsiveImages(Media $media): string {
            return $this->getPath($media) . 'responsive/';
        }
    }
