<?php
    namespace FeenstraDigital\LaravelCMS\Media\Support;

    use FeenstraDigital\LaravelCMS\Media\InteractsWithMedia;
    use FeenstraDigital\LaravelCMS\Media\HasMedia;
    use Illuminate\Database\Eloquent\Model;

    class SettingsPropertyMediaContainer extends Model implements HasMedia {
        use InteractsWithMedia;
    }