<?php

namespace Feenstra\CMS\Pagebuilder\Models;

use Feenstra\CMS\I18n\Interfaces\TranslatableInterface;
use Feenstra\CMS\I18n\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;
use Feenstra\CMS\Pagebuilder\Enums\PageTypeEnum;
use Feenstra\CMS\Pagebuilder\Http\Controllers\DynamicPageController;
use Feenstra\CMS\Pagebuilder\Support\PageRenderer;

class Page extends Model implements TranslatableInterface {
    use Translatable;

    public PageRenderer $renderer;

    protected $translate = ['title'];

    protected $table = 'fd_cms_pages';

    protected $guarded = [];

    protected $casts = [
        'type' => PageTypeEnum::class,
        'pagebuilder' => 'array',
        'pageheader' => 'array',
        'options' => 'array'
    ];

    /**
     * Get the current page from the request context.
     */
    public static function current(): Page {
        return DynamicPageController::getCurrentPage();
    }

    public function render(): string {
        $this->renderer = new PageRenderer($this);
        return $this->renderer->render();
    }

    public function isTemplate() {
        return $this->type === PageTypeEnum::Template;
    }

    public function getRecord(): ?Model {
        $routeParams = collect(request()->route()->parameters())->only('slug', 'id')->toArray();

        if (isset($this->model) && class_exists($this->model)) {
            $record = $this->model::where($routeParams)->first();
            if ($record instanceof Model) {
                return $record;
            }
        }

        return null;
    }
}
