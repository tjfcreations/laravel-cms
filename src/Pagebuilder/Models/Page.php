<?php
    namespace FeenstraDigital\LaravelCMS\Pagebuilder\Models;

    use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use stdClass;
use FeenstraDigital\LaravelCMS\Pagebuilder\Enums\PageTypeEnum;
    use FeenstraDigital\LaravelCMS\Pagebuilder\Support\Block;
    use FeenstraDigital\LaravelCMS\Pagebuilder\Registry;

    class Page extends Model
    {
        protected $table = 'fd_cms_pages';

        protected $guarded = [];

        protected $casts = [
            'type' => PageTypeEnum::class,
            'pagebuilder' => 'array',
            'pageheader' => 'array',
        ];

        public function render(): string {
            $content = '';

            $data = new stdClass();
            if($this->isTemplate()) {
                $data->record = $this->getRecord();
            }
            
            if(is_array($this->pagebuilder)) {
                foreach($this->pagebuilder as $schema) {
                    $content .= $this->renderBlock($schema['type'], array_merge($schema['data'], ['page' => $data]));
                }
            }

            View::startSection('content');
            echo $content;
            View::stopSection();
            
            $html = view('layouts.app')->render();
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

        protected function renderBlock(string $type, array $data): string {
            foreach(Registry::blocks() as $block) {
                if($block->getType() !== $type) continue;
                
                return $block->render($data);
            }

            return '';
        }
    }