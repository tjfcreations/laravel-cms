<?php

namespace Feenstra\CMS\Common;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Feenstra\CMS\Common\Filament\Pages\LogViewer;

class CommonServiceProvider extends ServiceProvider {
    public function boot(): void {
        $this->setupLoginRoute();
        $this->setupLogViewerAuth();
    }

    protected function setupLoginRoute(): void {
        if (!Route::has('login')) {
            Route::redirect('/login', '/admin/login/')->name('login');
        }
    }

    protected function setupLogViewerAuth(): void {
        LogViewer::auth(function ($request) {
            return $request->user() && $request->user()->canAccessPanel();
        });
    }
}
