<?php

namespace Feenstra\CMS\Pagebuilder\Models;

use Feenstra\CMS\I18n\Interfaces\TranslatableInterface;
use Feenstra\CMS\I18n\Models\Locale;
use Feenstra\CMS\I18n\Traits\Translatable;
use Feenstra\CMS\Pagebuilder\Enums\PageStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Feenstra\CMS\Pagebuilder\Enums\PageTypeEnum;
use Feenstra\CMS\Pagebuilder\Http\Controllers\PageController;
use Feenstra\CMS\Pagebuilder\Support\PageRenderer;
use Feenstra\CMS\Pagebuilder\Helpers\PageHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Page extends Model implements TranslatableInterface {
    use Translatable;

    public PageRenderer $renderer;

    protected $translate = ['title'];

    protected $table = 'fd_cms_pages';

    protected $guarded = [];

    protected $cache = [];

    protected $casts = [
        'type' => PageTypeEnum::class,
        'status' => PageStatusEnum::class,
        'pagebuilder' => 'array',
        'options' => 'array'
    ];

    public function render(): string {
        $this->renderer = new PageRenderer($this);
        return $this->renderer->render();
    }

    /**
     * Check if this page is the home page.
     */
    public function isHome() {
        return empty(trim($this->path));
    }

    /**
     * Check if this page is a template page.
     */
    public function isTemplate() {
        return $this->type === PageTypeEnum::Template;
    }

    /**
     * Check if this page is a draft page (opposite of published).
     */
    public function isDraft() {
        return !$this->isPublished();
    }

    /**
     * Check if this page is a published page (opposite of draft).
     */
    public function isPublished() {
        return $this->status === PageStatusEnum::Published;
    }

    /**
     * Get the record that belongs to this template page (memoized).
     */
    public function getRecord(array $params): ?Model {
        return once(function () use ($params) {
            if (isset($this->model) && class_exists($this->model)) {
                $record = $this->model::where($params)->first();
                if ($record instanceof Model) {
                    return $record;
                }
            }

            return null;
        });
    }

    /**
     * Get the record for the current request that belongs to this template page (memoized).
     */
    public function getRequestRecord(): Model {
        return once(function () {
            $routeParams = collect(request()->route()->parameters())->only('slug', 'id')->toArray();
            $record = $this->getRecord($routeParams);

            if (!$record) {
                throw new NotFoundHttpException();
            }

            return $record;
        });
    }

    /**
     * Find a page by its slug (memoized).
     */
    public static function findBySlug(string $slug): ?Page {
        return once(fn() => self::where('slug', $slug)->first());
    }

    /**
     * Find a page by its ID (memoized).
     */
    public static function findById($id, $columns = ['*']) {
        return once(fn() => self::findOrFail($id, $columns));
    }

    public function localizedUrl(?Locale $targetLocale = null, ?Model $record = null): string {
        if (!$record && $this->isTemplate()) {
            $record = $this->getRequestRecord();
        }

        return PageHelper::getLocalizedUrl($this, $targetLocale, $record);
    }
}
