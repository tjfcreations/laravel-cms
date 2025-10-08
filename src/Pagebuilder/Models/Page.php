<?php

namespace Feenstra\CMS\Pagebuilder\Models;

use Feenstra\CMS\I18n\Interfaces\TranslatableInterface;
use Feenstra\CMS\I18n\Models\Locale;
use Feenstra\CMS\I18n\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;
use Feenstra\CMS\Pagebuilder\Enums\PageTypeEnum;
use Feenstra\CMS\Pagebuilder\Http\Controllers\PageController;
use Feenstra\CMS\Pagebuilder\Support\PageRenderer;
use Feenstra\CMS\Pagebuilder\Helpers\PageHelper;

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

    public function render(): string {
        $this->renderer = new PageRenderer($this);
        return $this->renderer->render();
    }

    public function isTemplate() {
        return $this->type === PageTypeEnum::Template;
    }

    public function getRecord(array $params): ?Model {
        if (isset($this->model) && class_exists($this->model)) {
            $record = $this->model::where($params)->first();
            if ($record instanceof Model) {
                return $record;
            }
        }

        return null;
    }

    public function getCurrentRecord(): ?Model {
        $routeParams = collect(request()->route()->parameters())->only('slug', 'id')->toArray();
        return $this->getRecord($routeParams);
    }

    public function localizedUrl(?Locale $targetLocale = null, ?Model $record = null): string {
        return PageHelper::getLocalizedUrl($this, $targetLocale, $record ?? $this->getCurrentRecord());
    }
}
