<?php

namespace Feenstra\CMS\Pagebuilder\Helpers;

use Feenstra\CMS\Pagebuilder\Http\Controllers\PageController;
use Feenstra\CMS\I18n\Models\Locale;
use Feenstra\CMS\Pagebuilder\Models\Page;
use Illuminate\Database\Eloquent\Model;

class PageHelper {
    public function current() {
        return PageController::currentPage();
    }

    public static function getLocalizedUrl(Page $page, ?Locale $targetLocale = null, ?Model $record = null): string {
        $defaultLocale = Locale::getDefault();
        $currentLocale = PageController::currentLocale();
        $targetLocale = $targetLocale ?? $currentLocale;

        $path = $page->path;
        if ($page->isTemplate()) {
            $attributes = $record->toArray();
            $path = trim(preg_replace_callback('/\{(\w+)\}/', fn($m) => $attributes[$m[1]] ?? $m[0], $path), '/');
        }

        if ($targetLocale->is($defaultLocale)) {
            return url($path);
        } else {
            return url($targetLocale->hreflang . '/' . $path);
        }
    }
}
