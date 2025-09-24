<?php

namespace FeenstraDigital\LaravelCMS\Media\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Enums\MediaSizeEnum;

class MediaEmbed extends Model
{
    protected function casts() {
        return [
            'oembed_data' => 'array'
        ];
    }

    public function item() {
        return $this->hasOne(MediaItem::class);
    }

    public function getTitle() {
        return $this->oembed_data['title'];
    }

    public function getProviderName() {
        return $this->oembed_data['provider_name'];
    }

    public function getThumbnailUrl($size = MediaSizeEnum::MD) {
        $thumbnail_url = $this->oembed_data['thumbnail_url'];

        switch (strtolower($this->getProviderName())) {
            case 'youtube':
                // use version without black bars for youtube thumbnails
                $replace_jpg = ($size < MediaSizeEnum::SM->value) ? 'mqdefault.jpg' : 'maxresdefault.jpg';
                $thumbnail_url = Str::replace('hqdefault.jpg', $replace_jpg, $thumbnail_url);
                break;       
        }

        return $thumbnail_url;
    }

    public function getHTML() {
        return $this->oembed_data['html'];
    }
}
