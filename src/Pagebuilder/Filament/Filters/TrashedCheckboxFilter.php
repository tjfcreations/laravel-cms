<?php

namespace Feenstra\CMS\Pagebuilder\Filament\Filters;

use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;

class TrashedCheckboxFilter extends Filter {
    protected function setUp(): void {
        parent::setUp();

        // $this
        //     ->label('Inactieve pagina\'s')
        //     ->query(fn(Builder $query): Builder => $query->withTrashed());
    }
}
