<?php
    namespace FeenstraDigital\LaravelCMS\Pagebuilder\Models;

use FeenstraDigital\LaravelCMS\Locale\Interfaces\TranslatableInterface;
use FeenstraDigital\LaravelCMS\Locale\Traits\Translatable;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\View;
    use stdClass;
    use FeenstraDigital\LaravelCMS\Pagebuilder\Enums\PageTypeEnum;
    use FeenstraDigital\LaravelCMS\Pagebuilder\Support\Block;
    use FeenstraDigital\LaravelCMS\Pagebuilder\Registry;
use FeenstraDigital\LaravelCMS\Pagebuilder\ShortcodeProcessor;
use FeenstraDigital\LaravelCMS\Pagebuilder\Support\RecordWrapper;
use FeenstraDigital\LaravelCMS\Pagebuilder\Support\PageData;

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

        public function render(): string {
            $content = '';

            // compile page data
            $pageData = new PageData($this);

            if($this->isTemplate()) {
                $pageData->setRecord($this->getRecord());
            }

            // render blocks
            if(is_array($this->pagebuilder)) {
                foreach($this->pagebuilder as $schema) {
                    $content .= $this->renderBlock($schema['type'], $schema['data'], $pageData);
                }
            }

            View::startSection('content');
            echo $content;
            View::stopSection();
            
            $html = view('layouts.app', $pageData->toArray())->render();
            return $html;
        }

        public function getRecord(): ?Model {
            $routeParams = collect(request()->route()->parameters())->only('slug', 'id')->toArray();
            
            if(isset($this->model) && class_exists($this->model)) {
                return $this->model::where($routeParams)->first();
                if($record instanceof Model) {
                    return $record;
                }
            }

            return null;
        }

        public function isTemplate(): bool {
            return $this->type === PageTypeEnum::Template;
        }

        protected function renderBlock(string $type, array $schemaData, PageData $pageData): string {
            foreach(Registry::blocks() as $block) {
                if($block->getType() !== $type) continue;
                
                return $block->render($pageData->toBlockDataArray($block, $schemaData));
            }

            return '';
        }
    }