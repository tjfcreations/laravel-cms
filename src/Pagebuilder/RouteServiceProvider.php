<?php

namespace Feenstra\CMS\Pagebuilder;

use Illuminate\Support\ServiceProvider;
use Feenstra\CMS\Pagebuilder\Models\Page;
use Feenstra\CMS\Pagebuilder\Http\Controllers\PageController;
use Feenstra\CMS\Pagebuilder\Middleware\SetLocale;
use Feenstra\CMS\I18n\Models\Locale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Collection;

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
                foreach (Page::all() as $page) {
                    // don't register routes for error pages
                    if ($page->isErrorPage()) continue;

                    static::registerPageRoute($page, [PageController::class, 'show']);
                }

                // fallback route for pages that were newly created
                $this->registerRoute('/{any}', [PageController::class, 'handleDynamicFallback'], function ($route) {
                    return $route->where('any', '.*');
                });
            });
    }

    public static function registerPageRoute(Page $page, $action, ?callable $modifier = null): void {
        static::registerRoute($page->path, $action, function ($route) use ($page, $modifier) {
            $route->defaults('pageId', $page->id);
            if (is_callable($modifier)) {
                $modifier($route);
            }
        });
    }
    /**
     * Register a route with or without hreflang prefix.
     */
    public static function registerRoute(string $path, $action, ?callable $modifier = null): void {
        $routes = [];

        // register normal route
        $routes[] = Route::get($path, $action);

        // register additional hreflang route if applicable
        if (!empty(static::getHrefLangs())) {
            $routes[] = Route::get('/{hreflang}/' . ltrim($path, '/'), $action)
                ->where('hreflang', static::getHrefLangPattern());
        }

        if (is_callable($modifier)) {
            foreach ($routes as $route) {
                $modifier($route);
            }
        }
    }

    protected static function getHrefLangs() {
        return once(fn() => Locale::pluck('hreflang')->toArray());
    }

    protected static function getHrefLangPattern(): string {
        return once(fn() => implode('|', static::getHrefLangs()));
    }
}
