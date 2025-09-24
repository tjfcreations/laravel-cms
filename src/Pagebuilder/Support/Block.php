<?php
    namespace FeenstraDigital\LaravelCMS\Pagebuilder\Support;

    use Illuminate\Support\Collection;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Blade;
    use FeenstraDigital\LaravelCMS\Pagebuilder\Registry;
    use FeenstraDigital\LaravelCMS\Pagebuilder\ShortcodeProcessor;

    abstract class Block {
        public static string $view;
        public static string $label;
        public static ?string $icon = null;

        public function schema(): array {
            return [];
        }

        public function with(array $data): array {
            return $data;
        }
   
        public function quickSelect(): ?QuickSelect {
            return null;
        }

        public function render(array $data = []): string {
            $data = $this->getData($data);

            return Blade::render(static::$view, $data);
        }

        public function getData(array $data): array {
            $quickSelect = $this->quickSelect();
            
            if($quickSelect) {
                $data['records'] = $quickSelect->getRecords($data);
            }

            $data = $this->with($data);
            $data = $this->resolveShortcodes($data);

            return $data;
        }

        public function getType(): string {
            return Str::snake(class_basename(static::class));
        }

        public function getSchema(): array {
            $schema = $this->schema();

            // prepend quickselect component
            $quickSelect = $this->quickSelect();
            if($quickSelect) {
                array_unshift($schema, $quickSelect->toFormComponent());
            }
            
            return $schema;
        }

        public function resolveShortcodes(array $data): array {
            foreach($data as $key => &$value) {
                if(is_string($value)) {
                    $value = ShortcodeProcessor::resolve($value, $data);
                }
            }

            return $data;
        }
    }