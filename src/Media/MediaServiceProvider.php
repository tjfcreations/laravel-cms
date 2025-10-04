<?php

namespace Feenstra\CMS\Media;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Feenstra\CMS\Media\Http\Livewire\MediaUpload;
use Feenstra\CMS\Media\Commands\RegenerateCommand;
use Illuminate\Support\Facades\Config;
use Feenstra\CMS\Media\Support\HashedPathGenerator;
use Feenstra\CMS\Media\Support\HashedFileNamer;
use Feenstra\CMS\Media\Models\MediaItem;
use Feenstra\CMS\Media\Support\MediaGallery;
use Feenstra\CMS\Media\SettingsCasts\MediaGalleryCast;
use Illuminate\Support\ServiceProvider;

class MediaServiceProvider extends ServiceProvider {
    public function boot() {       
        $this->publishes([
            __DIR__.'/../../resources/media/js' => public_path('vendor/feenstradigital/laravel-cms/media/js'),
        ], 'laravel-assets');
        
        Config::set('media-library.media_model', MediaItem::class);
        Config::set('media-library.disk_name', config('media-gallery.disk_name', 'public'));
        Config::set('media-library.path_generator', HashedPathGenerator::class);
        Config::set('media-library.file_namer', HashedFileNamer::class);

        Config::set('settings.global_casts.' . MediaGallery::class, MediaGalleryCast::class);
    }
}
