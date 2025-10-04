<?php
    namespace Feenstra\CMS\Media\Support;

    use Feenstra\CMS\Media\Traits\HasMedia;
    use Feenstra\CMS\Media\Interfaces\HasMediaInterface;
    use Illuminate\Database\Eloquent\Model;

    class SettingsPropertyMediaContainer extends Model implements HasMediaInterface {
        use HasMedia;
    }