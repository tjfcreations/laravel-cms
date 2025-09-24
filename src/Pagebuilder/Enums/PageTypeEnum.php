<?php
namespace FeenstraDigital\LaravelCMS\Pagebuilder\Enums;

use Filament\Support\Contracts\HasLabel;

enum PageTypeEnum: string implements HasLabel
{
    case Default = 'default';
    case Template = 'template';
    case Error = 'error';

    public function getLabel(): string {
        return match($this) {
            self::Template => 'Template',
            self::Default => 'Standaardpagina',
            self::Error => 'Foutpagina'
        };
    }
}
