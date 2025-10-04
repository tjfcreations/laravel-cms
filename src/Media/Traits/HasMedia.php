<?php

namespace Feenstra\CMS\Media\Traits;

use Spatie\MediaLibrary\InteractsWithMedia as SpatieInteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Feenstra\CMS\Media\Models\MediaItem;
use Spatie\Image\Enums\Fit;

trait HasMedia {
    use SpatieInteractsWithMedia;

    public function getMediaModel(): string {
        return MediaItem::class;
    }

    public function registerMediaCollections(): void {
        $this->addMediaCollection('default')
            ->useDisk(config('media-gallery.disk_name'));
    }

    public function registerMediaConversions(?Media $media = null): void {
        $this->addMediaConversion('xs')
            ->width(180)
            ->height(180)
            ->fit(Fit::Contain)
            ->sharpen(10);

        $this->addMediaConversion('sm')
            ->width(360)
            ->height(360)
            ->fit(Fit::Contain)
            ->sharpen(10);

        $this->addMediaConversion('md')
            ->width(720)
            ->height(720)
            ->fit(Fit::Contain);

        $this->addMediaConversion('lg')
            ->width(1080)
            ->height(1080)
            ->fit(Fit::Contain);

        $this->addMediaConversion('xl')
            ->width(1440)
            ->height(1440)
            ->fit(Fit::Contain);
    }
}
