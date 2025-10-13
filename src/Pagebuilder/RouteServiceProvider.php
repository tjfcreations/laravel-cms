<?php

namespace Feenstra\CMS\Pagebuilder;

use Feenstra\CMS\Pagebuilder\Enums\PageTypeEnum;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Feenstra\CMS\Pagebuilder\Models\Page;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Feenstra\CMS\Pagebuilder\Http\Controllers\PageController;
use Feenstra\CMS\Pagebuilder\Middleware\SetLocale;
use Feenstra\CMS\I18n\Models\Locale;

class RouteServiceProvider extends ServiceProvider {
    public function boot(): void {
        try {
            $this->registerRoutes();
        } catch (\Exception $e) {
            // Database may not be available, ignore.
        }
    }

    public function registerRoutes(): void {
        if (app()->routesAreCached()) return;

        Route::middleware('web')
            ->group(function () {
                $hreflangs = Locale::pluck('hreflang')->toArray();
                $hreflangPattern = implode('|', $hreflangs);

                foreach (Page::all() as $page) {
                    foreach (Page::all() as $page) {
                        // register a normal get-route
                        Route::get($page->path, [PageController::class, 'show'])
                            ->defaults('pageId', $page->id);

                        // register a localized get-route
                        if (!empty($hreflangs)) {
                            Route::get('{hreflang}/' . ltrim($page->path, '/'), [PageController::class, 'show'])
                                ->defaults('pageId', $page->id)
                                ->where('hreflang', $hreflangPattern);
                        }
                    }
                }
            });
    }
}
