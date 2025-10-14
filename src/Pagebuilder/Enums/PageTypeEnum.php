<?php

namespace Feenstra\CMS\Pagebuilder\Enums;

use Filament\Support\Contracts\HasLabel;

enum PageTypeEnum: string implements HasLabel {
    case Default = 'default';
    case Generic = 'generic';
    case Template = 'template';
    case Error = 'error';

    public function getLabel(): string {
        return match ($this) {
            self::Default => 'default',
            self::Template => 'Template',
            self::Generic => 'Standaardpagina',
            self::Error => 'Foutpagina'
        };
    }
}
