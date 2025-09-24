<?php

namespace FeenstraDigital\LaravelCMS\Locale\Filament\Resources;

use FeenstraDigital\LaravelCMS\Locale\Filament\Resources\TranslationResource\Pages;
use FeenstraDigital\LaravelCMS\Locale\Models\Translation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use FeenstraDigital\LaravelCMS\Locale\Models\Locale;
use FeenstraDigital\LaravelCMS\Locale\Registry;

class TranslationResource extends Resource
{
    protected static ?string $slug = 'fd-cms-translations';

    protected static ?string $model = Translation::class;

    protected static ?string $navigationGroup = 'Vertalingen';
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $label = 'vertaling';
    protected static ?string $pluralLabel = 'vertalingen';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\ToggleButtons::make('is_global')
                    ->label('Globale vertaling')
                    ->helperText('Een globale vertaling kan overal op de site worden gebruikt.')
                    ->boolean()
                    ->inline()
                    ->grouped()
                    ->live()
                    ->default(true),
                Forms\Components\Select::make('locale')
                    ->label('Taal')
                    ->options(Locale::all()->pluck('name', 'code'))
                    ->placeholder('Kies een taal...')
                    ->required(),
                Forms\Components\TextInput::make('key')
                    ->label('Sleutel')
                    ->placeholder('title')
                    ->visible(fn(Get $get) => $get('is_global'))
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\Select::make('model')
                    ->label('Record')
                    ->options(function() {
                        $options = [];
                        
                        foreach(Registry::translatables() as $translatable) {
                            $groupLabel = $translatable->getGroupLabel();
                            $options[$groupLabel] = [];

                            $records = $translatable->query()->get();

                            foreach($records as $record) {
                                $data = ['model_type' => $record::class, 'model_id' => $record->id];
                                $value = self::packData($data)['model'];
                                $options[$groupLabel][$value] = $record->getLabel();
                            }
                        }

                        return $options;
                    })
                    ->searchable()
                    ->placeholder('Kies een record...')
                    ->visible(fn(Get $get) => !$get('is_global'))
                    ->required()
                    ->live(),
                Forms\Components\Select::make('key')
                    ->label('Attribute')
                    ->options(function(Get $get) {
                        $options = [];


                        $class = self::unpackData(['model' => $get('model')])['model_type'];
                        if(is_string($class) && class_exists($class)) {
                            $instance = new $class();
                            foreach($instance->getTranslatableAttributes() as $attribute) {
                                $options[$attribute] = $attribute;
                            }
                        }

                        return $options;
                    })
                    ->placeholder('Kies een attribute...')
                    ->visible(fn(Get $get) => !$get('is_global'))
                    ->disabled(fn(Get $get) => !$get('model'))
                    ->required(),
                Forms\Components\Textarea::make('value')
                    ->label('Vertaling')
                    ->columnSpanFull()
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTranslations::route('/'),
            'create' => Pages\CreateTranslation::route('/create'),
            'edit' => Pages\EditTranslation::route('/{record}/edit'),
        ];
    }

    /**
     * Unpack data, before updating the database.
     */
    public static function unpackData(array $data) {
        // model
        $model = @json_decode(@$data['model'], true);
        $data['model_type'] = @$model['model_type'];
        $data['model_id'] = @$model['model_id'];
        unset($data['model']);
        
        // is_global
        unset($data['is_global']);

        return $data;
    }

    /**
     * Pack data, before filling the form.
     */
    public static function packData(array $data) {
        // model
        if(isset($data['model_type']) && isset($data['model_id'])) {
            $data['model'] = json_encode(['model_type' => @$data['model_type'], 'model_id' => @$data['model_id']]);
            unset($data['model_type']);
            unset($data['model_id']);
        } else {
            $data['model'] = null;
        }

        // is_global
        $data['is_global'] = !isset($data['model']);

        return $data;
    }
}
