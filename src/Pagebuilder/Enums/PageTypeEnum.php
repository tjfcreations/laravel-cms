<?php
namespace FeenstraDigital\LaravelCMS\Pagebuilder\Enums;

use Filament\Support\Contracts\HasLabel;

enum PageTypeEnum: string implements HasLabel
{
    case Static = 'static';
    case Template = 'template';

    public function getLabel(): string {
        return match($this) {
            self::Static => 'Standaard',
            self::Template => 'Template',
        };
    }
}
