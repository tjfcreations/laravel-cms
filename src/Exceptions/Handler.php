<?php

namespace Feenstra\CMS\Exceptions;

use Feenstra\CMS\Pagebuilder\Http\Controllers\PageController;
use Illuminate\Foundation\Exceptions\Handler as BaseHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Feenstra\CMS\Pagebuilder\Models\Page;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Throwable;

class Handler extends BaseHandler {
    public function render($request, Throwable $e) {
        $statusCode = 500;

        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
        } else if ($e instanceof ModelNotFoundException) {
            $statusCode = 404;
        }

        $page = Page::where('error_code', $statusCode)->first();
        if ($page) {
            if ($page->isPublished() || Auth::user()) {
                $content = app(PageController::class)->show($page);
                return response($content, $statusCode);
            }
        }

        return response()->json([
            'message' => $e->getMessage(),
            'statusCode' => $statusCode
        ]);

        return parent::render($request, $e);
    }
}
