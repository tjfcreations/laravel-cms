<?php
    use Illuminate\Support\Facades\Route;
    use FeenstraDigital\LaravelCMS\Locale\Models\Locale;

    // web middleware is needed for sessions
    Route::middleware('web')->group(function() {
        Route::post('/locale', function() {
            $locale = request('locale');
            $supportedLocales = Locale::all()->pluck('code');

            if($supportedLocales->contains($locale)) {
                session(['locale' => $locale]);
            }

            return back();
        })->name('pagebuilder.locale.update');  
    });