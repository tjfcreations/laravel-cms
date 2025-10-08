<?php

namespace Feenstra\CMS\Pagebuilder\Http\Controllers;

use Feenstra\CMS\Pagebuilder\Models\Page;

class DynamicPageController {
    protected static Page $currentPage;

    public function show() {
        $pageId = request()->route('pageId');
        $page = Page::findOrFail($pageId) ?? Page::make();

        static::$currentPage = $page;

        return $page->render();
    }

    public static function getCurrentPage() {
        return static::$currentPage;
    }
}
