<?php
    namespace Feenstra\CMS\I18n\Http\Middleware;

    use Closure;
    use Illuminate\Support\Facades\App;

    class SetLocale {
        public function handle($request, Closure $next) {
            App::setLocale(session('locale', 'nl_NL'));

            return $next($request);
        }
    }