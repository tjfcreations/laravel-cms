<?php

namespace Feenstra\CMS\Pagebuilder\Filament\Resources;

use BackedEnum;
use Feenstra\CMS\Pagebuilder\Filament\Resources\MenuResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns;
use Filament\Tables;
use Feenstra\CMS\Pagebuilder\Models\Menu;
use Feenstra\CMS\Pagebuilder\Filament\Components\MenuItemRepeater;
use Feenstra\CMS\Pagebuilder\Enums\MenuLocationEnum;
use Filament\Resources\Resource;

class MenuResource extends Resource {
    protected static ?string $slug = 'fd-cms-menus';

    protected static ?string $model = Menu::class;

    protected static ?string $navigationGroup = 'Weergave';
    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?int $navigationSort = 100;

    protected static ?string $label = 'menu';
    protected static ?string $pluralLabel = 'menu\'s';


    public static function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Naam')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('location')
                    ->label('Locatie')
                    ->options(MenuLocationEnum::class)
                    ->required(),
                MenuItemRepeater::make('items')
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Columns\TextColumn::make('name')
                    ->label('Naam')
                    ->sortable()
                    ->searchable(),
                Columns\TextColumn::make('path')
                    ->label('Pad')
                    ->sortable()
                    ->searchable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('name', 'asc');
    }

    public static function getRelations(): array {
        return [
            //
        ];
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit')
        ];
    }
}
