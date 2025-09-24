<?php
    namespace FeenstraDigital\LaravelCMS\Pagebuilder\Support;

use FeenstraDigital\LaravelCMS\Pagebuilder\Models\Page;
use FeenstraDigital\LaravelCMS\Pagebuilder\ShortcodeProcessor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

    class PageData {
        protected Page $page;
        protected ?RecordWrapper $record;

        public function __construct(Page $page) {
            $this->page = $page;
            $this->record = null;
        }

        public function setRecord(Model $record): self {
            $this->record = $this->wrap($record);
            return $this;
        }

        public function toArray() {
            return [
                'page' => new RecordWrapper($this->page, $this),
                'record' => $this->record
            ];
        }

        public function toBlockDataArray(Block $block, array $schemaData): array {
            foreach($schemaData as &$value) {
                $value = ShortcodeProcessor::resolve($value, $this->toArray());
            }

            $blockData = (object) $schemaData;

            $quickSelect = $block->quickSelect();
            if($quickSelect) {
                $blockData->records = $quickSelect->getRecords($schemaData)
                    ->map(fn($r) => new RecordWrapper($r, $this));
            }

            return [
                ...$this->toArray(),
                'block' => $blockData
            ];
        } 

        protected function wrap(Model $record): RecordWrapper {
            return new RecordWrapper($record, $this);
        }
    }