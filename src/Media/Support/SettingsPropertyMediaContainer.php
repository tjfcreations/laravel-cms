<?php
    namespace FeenstraDigital\LaravelCMS\Media\Support;

    use FeenstraDigital\LaravelCMS\Media\Traits\HasMedia;
    use FeenstraDigital\LaravelCMS\Media\Interfaces\HasMediaInterface;
    use Illuminate\Database\Eloquent\Model;

    class SettingsPropertyMediaContainer extends Model implements HasMediaInterface {
        use HasMedia;
    }