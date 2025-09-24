<?php
namespace Tjall\Pagebuilder;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use FeenstraDigital\LaravelCMS\Pagebuilder\Models\Page;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use FeenstraDigital\LaravelCMS\Pagebuilder\Http\Controllers\DynamicPageController;
use FeenstraDigital\LaravelCMS\Pagebuilder\Middleware\SetLocale;

class DynamicPageServiceProvider extends ServiceProvider {
    public function boot(): void {
        try {
            $this->registerDynamicRoutes();
        } catch(\Exception $e) {
            // Database may not be available, ignore.
        }
    }

    public function registerDynamicRoutes(): void {
        if(app()->routesAreCached()) return;

        Route::middleware('web')
            ->group(function() {
                foreach (Page::all() as $page) {
                    Route::get($page->path, [DynamicPageController::class, 'show'])
                        ->defaults('pageId', $page->id);
                }
            });
    }
}