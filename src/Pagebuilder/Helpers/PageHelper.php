<?php

namespace Feenstra\CMS\Pagebuilder\Helpers;

use Feenstra\CMS\Pagebuilder\Http\Controllers\PageController;
use Feenstra\CMS\I18n\Models\Locale;
use Feenstra\CMS\Pagebuilder\Models\Page;
use Illuminate\Database\Eloquent\Model;
use Feenstra\CMS\Pagebuilder\Support\Facades\PageHeader;

class PageHelper {
    public function current() {
        return PageController::currentPage();
    }

    public function header() {
        return app(PageHeader::class);
    }

    public static function getLocalizedUrl(Page $page, ?Locale $targetLocale = null, ?Model $record = null): string {
        $defaultLocale = Locale::getDefault();
        $currentLocale = PageController::currentLocale();
        $targetLocale = $targetLocale ?? $currentLocale;

        $path = $page->path;
        if ($page->isTemplate()) {
            if (isset($record)) {
                // if record exists, replace placeholders in path with record attributes
                $attributes = $record->toArray();
                $path = trim(preg_replace_callback('/\{(\w+)\}/', fn($m) => $attributes[$m[1]] ?? $m[0], $path), '/');
            } else {
                // if record does not exist, use current request path
                $path = request()->path();
                if ($currentLocale && !$currentLocale->is($defaultLocale)) {
                    $path = trim(preg_replace('/^\/' . preg_quote($currentLocale->hreflang, '/') . '\//', '/', $path), '/');
                }
            }
        }

        if ($targetLocale->is($defaultLocale)) {
            return url($path);
        } else {
            return url($targetLocale->hreflang . '/' . $path);
        }
    }
}
