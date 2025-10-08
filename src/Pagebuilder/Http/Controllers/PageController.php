<?php

namespace Feenstra\CMS\Pagebuilder\Http\Controllers;

use Feenstra\CMS\Pagebuilder\Models\Page;
use Feenstra\CMS\I18n\Models\Locale;

class PageController {
    protected static Page $currentPage;
    protected static Locale $currentLocale;

    public function show() {
        $hreflang = request()->route('locale');
        if (!empty($hreflang)) {
            $locale = Locale::where('hreflang', $hreflang)->first();

            if ($locale) {
                static::$currentLocale = $locale;
            }
        }

        $pageId = request()->route('pageId');
        $page = Page::findOrFail($pageId) ?? Page::make();

        static::$currentPage = $page;

        return $page->render();
    }

    public static function currentLocale(): Locale {
        if (!isset(static::$currentLocale)) {
            static::$currentLocale = Locale::getDefault();
        }

        return static::$currentLocale;
    }

    public static function currentPage() {
        return static::$currentPage;
    }
}
