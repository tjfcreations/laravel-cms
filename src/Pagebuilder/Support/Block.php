<?php

namespace Feenstra\CMS\Pagebuilder\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Blade;
use Feenstra\CMS\Pagebuilder\Registry;
use Feenstra\CMS\Pagebuilder\Shortcodes\ShortcodeProcessor;
use Filament\Forms\Form;

abstract class Block {
    public static string $view;
    public static string $label;
    public static ?string $icon = null;

    public static function findByType(string $type) {
        foreach (Registry::blocks() as $block) {
            if ($block->getType() !== $type) {
                continue;
            }

            return $block;
        }
    }

    public function schema(): array {
        return [];
    }

    public function form(Form $form): Form {
        return $form;
    }

    public function with(array $data): array {
        return $data;
    }

    public function quickSelect(): ?QuickSelect {
        return null;
    }

    public function render(array $data, PageRenderer $pageRenderer): string {
        // process shortcodes inside data
        foreach ($data as &$value) {
            if (is_string($value)) {
                $value = $pageRenderer->getShortcodeProcessor()->process($value);
            }
        }

        $blockData = $this->mutateDataBeforeRender($data);
        $mergedData = [
            ...$pageRenderer->getData(),
            'block' => (object) $blockData
        ];

        return Blade::render(static::$view, $mergedData);
    }

    public function mutateDataBeforeRender(array $data): array {
        $quickSelect = $this->quickSelect();
        if ($quickSelect) {
            $data['records'] = $quickSelect->getRecords($data);
        }

        $data = $this->with($data);

        return $data;
    }

    public function getType(): string {
        return Str::snake(class_basename(static::class));
    }

    public function getForm(Form $form): Form {
        return $this->form($form);
    }

    public function getSchema(): array {
        $schema = $this->schema();

        // prepend quickselect component
        $quickSelect = $this->quickSelect();
        if ($quickSelect) {
            array_unshift($schema, $quickSelect->toFormComponent());
        }

        return $schema;
    }
}
