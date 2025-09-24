<?php

namespace FeenstraDigital\LaravelCMS\Media;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use FeenstraDigital\LaravelCMS\Media\Http\Livewire\MediaUpload;
use FeenstraDigital\LaravelCMS\Media\Commands\RegenerateCommand;
use Illuminate\Support\Facades\Config;
use FeenstraDigital\LaravelCMS\Media\Support\HashedPathGenerator;
use FeenstraDigital\LaravelCMS\Media\Support\HashedFileNamer;
use FeenstraDigital\LaravelCMS\Media\Models\MediaItem;
use FeenstraDigital\LaravelCMS\Media\Support\MediaGallery;
use FeenstraDigital\LaravelCMS\Media\SettingsCasts\MediaGalleryCast;
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
