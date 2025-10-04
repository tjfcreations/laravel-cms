<?php

namespace Feenstra\CMS\Pagebuilder\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PageStatusEnum: string implements HasLabel, HasColor {
    case Draft = 'draft';
    case Published = 'published';

    public function getLabel(): string {
        return match ($this) {
            self::Draft => 'Concept',
            self::Published => 'Gepubliceerd',
        };
    }

    public function getColor(): array|string|null {
        return match ($this) {
            self::Draft => Color::Gray,
            self::Published => 'primary',
        };
    }
}
