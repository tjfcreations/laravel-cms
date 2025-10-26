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

    public function url(mixed $slugOrObject, ?Model $record = null) {
        return once(function () use ($slugOrObject, $record) {
            $pageSlug = null;
            $class = null;

            // determine if input is a slug string, a class name, or a model instance
            if (is_string($slugOrObject)) {
                if (class_exists($slugOrObject)) {
                    $class = $slugOrObject;
                } else {
                    $pageSlug = $slugOrObject;
                }
            } else if ($slugOrObject instanceof Model) {
                $record = $slugOrObject;
            }

            // determine slug from class or record if not explicitly provided
            if (empty($pageSlug)) {
                if (isset($class)) {
                    $pageSlug = strtolower(class_basename($class)) . '.index';
                } else if (isset($record)) {
                    $pageSlug = strtolower(class_basename($record)) . '.show';
                }
            }

            if (isset($pageSlug)) {
                $page = Page::findBySlug($pageSlug);
            }

            if (isset($page)) {
                return self::getLocalizedUrl($page, null, $record);
            }

            return null;
        });
    }

    public static function getLocalizedUrl(Page $page, ?Locale $targetLocale = null, ?Model $record = null): string {
        return once(function () use ($page, $targetLocale, $record) {
            $defaultLocale = Locale::getDefault();
            $currentLocale = PageController::currentLocale();
            $targetLocale = $targetLocale ?? $currentLocale;

            $path = $page->path;
            if ($page->isTemplate() && isset($record)) {
                // if record exists, construct target url from page path template
                $attributes = $record->toArray();
                $path = preg_replace_callback('/\{(\w+)\}/', fn($m) => $attributes[$m[1]] ?? $m[0], $path);
            } else if ($page->isTemplate() || $page->isErrorPage()) {
                // construct target url from current url
                $path = '/' . trim(request()->path(), '/');
                $path = preg_replace('/^\/' . preg_quote($currentLocale->hreflang, '/') . '\//', '/', $path);
            }

            if ($targetLocale->is($defaultLocale)) {
                return url($path);
            } else {
                return url($targetLocale->hreflang . '/' . trim($path, '/'));
            }
        });
    }
}
