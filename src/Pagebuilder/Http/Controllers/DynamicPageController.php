<?php
    namespace Feenstra\CMS\Pagebuilder\Http\Controllers;

    use Feenstra\CMS\Pagebuilder\Models\Page;

    class DynamicPageController {
        public function show()  {
            $pageId = request()->route('pageId');

            $page = Page::findOrFail($pageId);

            return $page->render();
        }
    }