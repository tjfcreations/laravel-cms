<?php
    namespace FeenstraDigital\LaravelCMS\Pagebuilder\Http\Controllers;

    use FeenstraDigital\LaravelCMS\Pagebuilder\Models\Page;

    class DynamicPageController {
        public function show()  {
            $pageId = request()->route('pageId');

            $page = Page::findOrFail($pageId);

            return $page->render();
        }
    }