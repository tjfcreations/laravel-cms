<?php

use Feenstra\CMS\Pagebuilder\Helpers\PageHelper;

if (!function_exists('page')) {
    function page() {
        return app(PageHelper::class);
    }
}
