<?php

namespace Feenstra\CMS\Media\Interfaces;

use Spatie\MediaLibrary\HasMedia as SpatieHasMedia;

interface HasMediaInterface extends SpatieHasMedia {
    public function getMediaModel(): string;
}
