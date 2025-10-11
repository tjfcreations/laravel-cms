<?php

namespace Feenstra\CMS\Pagebuilder\Http\Controllers;

use Feenstra\CMS\Pagebuilder\Models\Page;
use Feenstra\CMS\I18n\Models\Locale;
use Illuminate\Support\Facades\Auth;

class PageController {
    protected static Page $currentPage;
    protected static Locale $currentLocale;

    public function show(?Page $page = null) {
        $hreflang = request()->route('locale');
        if (!empty($hreflang)) {
            $locale = Locale::where('hreflang', $hreflang)->first();

            if ($locale) {
                static::$currentLocale = $locale;
            }
        }

        if (!$page) {
            $pageId = request()->route('pageId');
            $page = Page::findOrFail($pageId) ?? Page::make();
        }

        if ($page->isDraft() && !Auth::user()) {
            return abort(404);
        }

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
