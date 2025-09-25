<?php
    namespace FeenstraDigital\LaravelCMS\Pagebuilder\Shortcodes;

    use FeenstraDigital\LaravelCMS\Pagebuilder\Support\PageRenderer;
    use Illuminate\Database\Eloquent\Model;

    class ShortcodeProcessingWrapper
    {
        protected Model $record;
        protected ShortcodeProcessor $shortcodeProcessor;

        public function __construct(Model $record, ShortcodeProcessor $shortcodeProcessor)
        {
            $this->record = $record;
            $this->shortcodeProcessor = $shortcodeProcessor;
        }

        public function __get($name) {
            $value = $this->record->$name;
            return $this->resolve($name, $value);
        }

        public function getRecord() {
            return $this->record;
        }

        protected function resolve($name, $value)
        {
            if(is_string($value)) {
                return $this->shortcodeProcessor->process($value);
            }

            return $value;
        }
    }
