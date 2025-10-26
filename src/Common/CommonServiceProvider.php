<?php

namespace Feenstra\CMS\Common;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class CommonServiceProvider extends ServiceProvider {
    public function boot(): void {
        if (!Route::has('login')) {
            Route::redirect('/login', '/admin/login/')->name('login');
        }
    }
}
