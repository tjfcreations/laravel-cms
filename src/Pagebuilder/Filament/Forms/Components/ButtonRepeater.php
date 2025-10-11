<?php

namespace Feenstra\CMS\Pagebuilder\Filament\Forms\Components;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Forms;

class ButtonRepeater extends Repeater {
    protected function setUp(): void {
        parent::setUp();

        $this
            ->label('Knoppen')
            ->extraItemActions([
                $this->getItemEditAction(),
            ])
            ->addActionLabel('Knop toevoegen')
            ->addActionAlignment('left');
    }

    protected function getItemEditAction(): Action {
        return Action::make('edit')
            ->label('Bewerk')
            ->icon('heroicon-s-pencil-square')
            ->color('primary')
            ->modalSubmitActionLabel('Opslaan')
            ->fillForm(function (array $arguments): array {
                $state = $this->getState();
                return $state[$arguments['item']] ?? [];
            })
            ->form(function (Form $form) {
                return $this->getEditForm($form);
            })
            ->action(function (array $arguments, array $data): void {
                $state = $this->getState();
                $state[$arguments['item']] = array_merge($state[$arguments['item']] ?? [], $data);
                $this->state($state);
            });
    }

    protected function getEditForm(Form $form) {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('label')
                    ->label('Label')
                    ->placeholder('Mijn knop')
                    ->required()
                    ->live(),

                Forms\Components\Select::make('type')
                    ->label('Type')
                    ->options([
                        'page' => 'Pagina',
                        'external_url' => 'Externe URL',
                        'title' => 'Titel',
                        'mailto' => 'E-mailadres',
                        'tel' => 'Telefoon',
                    ])
                    ->live()
                    ->default('page')
                    ->required()
                    ->selectablePlaceholder(false),
            ]);
    }
}
