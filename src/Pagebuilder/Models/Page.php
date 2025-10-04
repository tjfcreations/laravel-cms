<?php
    namespace Feenstra\CMS\Pagebuilder\Models;

    use Feenstra\CMS\Locale\Interfaces\TranslatableInterface;
    use Feenstra\CMS\Locale\Traits\Translatable;
    use Illuminate\Database\Eloquent\Model;
    use Feenstra\CMS\Pagebuilder\Enums\PageTypeEnum;
    use Feenstra\CMS\Pagebuilder\Support\PageRenderer;

    class Page extends Model implements TranslatableInterface
    {
        use Translatable;

        protected $translate = ['title'];

        protected $table = 'fd_cms_pages';

        protected $guarded = [];

        protected $casts = [
            'type' => PageTypeEnum::class,
            'pagebuilder' => 'array',
            'pageheader' => 'array',
        ];

        public function render(): string
        {
            return (new PageRenderer($this))->render();
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