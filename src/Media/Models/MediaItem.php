<?php

namespace FeenstraDigital\LaravelCMS\Media\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaItem extends Media {
    /** @use HasFactory<\Database\Factories\MediaItemFactory> */
    use HasFactory;

    const CONVERSIONS = [
        'xs' => ['width' => 180, 'height' => 180, 'fit' => Fit::Contain, 'sharpen' => 10 ],
        'sm' => ['width' => 360, 'height' => 360, 'fit' => Fit::Contain, 'sharpen' => 10 ],
        'md' => ['width' => 720, 'height' => 720, 'fit' => Fit::Contain ],
        'lg' => ['width' => 1080, 'height' => 1080, 'fit' => Fit::Contain ],
        'xl' => ['width' => 1440, 'height' => 1440, 'fit' => Fit::Contain ],
    ];

    protected $table = 'fd_cms_media_items';

    public function getUrl(string $conversionName = ''): string {
        if (!is_int($this->size) || $this->size <= 1) {
            if(app()->environment('local')) {
                $width = 720;
                $ratio = (($this->id % 9) + 6) / 10;
                $height = $width * $ratio;
                return "https://picsum.photos/seed/{$this->id}/{$width}/{$height}";
            }
        }

        if($this->hasGeneratedConversion($conversionName)) {
            return parent::getUrl($conversionName);
        }

        return parent::getUrl();
    }

    public function getDataUrl(string $conversionName = '') {
        $path = $this->getPath($conversionName);

        $mime = mime_content_type($path);
        $contents = file_get_contents($path);
        $base64 = base64_encode($contents);

        return "data:{$mime};base64,{$base64}";
    }

    public function isVideo() {
        return false;
    }

    public function serialize() {
        $sources = $this->getAvailableSources();

        return [
            'uuid' => $this->uuid,
            'sources' => $sources,
            'meta' => $this->getMeta()
        ];
    }

    public function getAvailableSources() {
        $sources = [];

        $shouldSaveSizes = false;
        $conversions = $this->getGeneratedConversions();
        $sizes = $this->getCustomProperty('sizes');
        foreach ($conversions as $conversionName => $isGenerated) {
            if(!$isGenerated) continue;
            
            $size = [];

            try {
                if(!isset($sizes[$conversionName])) {
                    list($width, $height) = getimagesize($this->getPath($conversionName));
                    $sizes[$conversionName] = [$width, $height];
                    $shouldSaveSizes = true;
                }

                $size = $sizes[$conversionName];
            } catch(\Exception $e) {
                continue;
            }
            
            if(isset($size[0]) && isset($size[1])) {
                $sources[$conversionName] = [ 
                    'url' => $this->getUrl($conversionName),
                    'width' => $size[0],
                    'height' => $size[1]
                ];
            }
        }

        if($shouldSaveSizes) {
            $this->setCustomProperty('sizes', $sizes);
            $this->save();
        }

        return $sources;
    }

    public function getDescription() {
        return @$this->getMeta()['description'];
    }

    public function getMeta() {
        return $this->getCustomProperty('meta', []);
    }

    public function setMeta(array $meta) {
        $this->setCustomProperty('meta', $meta);

        return $this;
    }
}
