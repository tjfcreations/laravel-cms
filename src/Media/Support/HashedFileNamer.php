<?php
    namespace Feenstra\CMS\Media\Support;

    use Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer as SpatieDefaultFileNamer;

    class HashedFileNamer extends SpatieDefaultFileNamer
    {
        public function originalFileName(string $fileName): string
        {
            return md5(pathinfo($fileName, PATHINFO_FILENAME));
        }
    }