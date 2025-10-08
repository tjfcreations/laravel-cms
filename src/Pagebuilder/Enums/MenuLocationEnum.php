<?php

namespace Feenstra\CMS\Pagebuilder\Enums;

use Filament\Support\Contracts\HasLabel;

enum MenuLocationEnum: string implements HasLabel {
    case Primary = 'primary';
    case Footer = 'footer';

    public function getLabel(): string {
        return match ($this) {
            self::Primary => 'Hoofdmenu',
            self::Footer => 'Footer',
        };
    }
}
