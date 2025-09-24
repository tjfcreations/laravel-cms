<?php
    namespace FeenstraDigital\LaravelCMS\Pagebuilder\Support;

use FeenstraDigital\LaravelCMS\Pagebuilder\ShortcodeProcessor;
use Illuminate\Database\Eloquent\Model;

    class RecordWrapper
    {
        protected Model $record;
        protected PageData $data;

        public function __construct(Model $record, PageData $data)
        {
            $this->record = $record;
            $this->data = $data;
        }

        public function __get($name) {
            $value = $this->record->$name;
            return $this->resolve($name, $value);
        }

        public function __call($name, $arguments) {
            $result = $this->record->$name(...$arguments);
            return $this->resolve($name, $result);
        }

        protected function resolve($name, $value)
        {
            if(is_string($value)) {
                return ShortcodeProcessor::resolve($value, $this->data->toArray());
            }

            return $value;
        }
    }
