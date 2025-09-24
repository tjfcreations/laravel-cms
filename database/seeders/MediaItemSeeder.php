<?php

namespace FeenstraDigital\LaravelCMS\Media\Database\Seeders;

use FeenstraDigital\LaravelCMS\Media\Models\MediaGallery;
use FeenstraDigital\LaravelCMS\Media\Models\MediaItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MediaItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach(MediaGallery::all() as $gallery) {
            MediaItem::factory(rand(0, 6))->create()->each(function(MediaItem $item) use($gallery) {
                // $path = resource_path('dev/placeholders/'.rand(0,4).'.jpg');
                // $item->setResourcePath($path);
                $gallery->items()->attach($item);
            });
            
            $gallery->spotlight_id = MediaItem::inRandomOrder()->first()?->id;
            if(!$gallery->spotlight_id) continue;
            $gallery->save();
        }
    }
}
