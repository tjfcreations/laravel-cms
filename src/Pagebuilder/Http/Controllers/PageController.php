<?php

namespace Feenstra\CMS\Pagebuilder\Http\Controllers;

use Feenstra\CMS\Pagebuilder\Models\Page;
use Feenstra\CMS\I18n\Models\Locale;
use Feenstra\CMS\Pagebuilder\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;

class PageController {
    protected static Page $currentPage;
    protected static Locale $currentLocale;

    public function show(?Page $page = null) {
        $hreflang = request()->route('hreflang');
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
            return;
        }

        static::$currentPage = $page;

        return $page->render();
    }

    /**
     * Fallback method to show a page that was newly created and not yet
     * saved in the cache.
     */
    public function handleDynamicFallback() {
        // Query pages updated in the last 60 minutes
        $pages = Page::where('updated_at', '>=', now()->subMinutes(60))->get();
        if ($pages->isEmpty()) return abort(404);

        $path = '/' . request()->path();

        $routes = [];
        $symfonyRoutes = new RouteCollection();
        $context = new RequestContext('/');
        $matcher = new UrlMatcher($symfonyRoutes, $context);

        foreach ($pages as $page) {
            if ($page->isErrorPage()) continue;
            if ($page->isDraft() && !Auth::user()) continue;

            RouteServiceProvider::registerPageRoute($page, [PageController::class, 'show'], function ($route) use (&$routes, $symfonyRoutes) {
                $index = $symfonyRoutes->count();
                $routes[$index] = $route;
                $symfonyRoutes->add($index, $route->toSymfonyRoute());
            });
        }

        // check if any route matches
        try {
            $match = $matcher->match($path);
            $route = $routes[$match['_route']];

            $page = Page::find(intval($route->defaults['pageId']));
            if (!$page) return;

            $route = request()->route();
            foreach (collect($match)->except('_route') as $key => $value) {
                $route->setParameter($key, $value);
            }

            Log::debug('Succesfully matched uncached page', ['path' => $path, 'pageId' => $page->id]);

            return $this->show($page);
        } catch (\Exception $e) {
            return abort(404, $e->getMessage());
        }

        return abort(404);
    }

    protected function resolvePathParameters(string $pattern, string $path): array {
        $routes = new RouteCollection();
        $routes->add('route', new Route($pattern));

        $context = new RequestContext('/');
        $matcher = new UrlMatcher($routes, $context);

        return $matcher->match($path);
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
