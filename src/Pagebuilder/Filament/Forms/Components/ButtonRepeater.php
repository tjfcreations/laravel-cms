<?php

namespace Feenstra\CMS\Pagebuilder\Filament\Forms\Components;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Forms\Components\Component;

class ButtonRepeater extends Repeater {
    protected function setUp(): void {
        parent::setUp();

        $this
            ->label('Knoppen')
            ->addActionLabel('Knop toevoegen')
            ->addActionAlignment('left')
            ->itemLabel(fn(array $state) => (@$state['link']['label'] ?? '(geen label)'))
            ->extraItemActions([
                $this->getItemEditAction(),
            ])
            ->extraAttributes(['class' => 'fd-cms-repeater-no-content']);
    }

    public function getAddAction(): Action {
        return parent::getAddAction()
            ->form(function (Form $form) {
                return $this->getEditForm($form);
            })
            ->modalSubmitActionLabel('Toevoegen')
            ->action(function (array $data, Component $component): void {
                $state = $component->getState();

                $newUuid = $component->generateUuid();
                $state[$newUuid] = $data;

                $component->state($state);
            });
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
                Link::make('link')
                    ->placeholder('Nieuwe knop')
                    ->email()
                    ->externalUrl()
                    ->tel()
            ]);
    }
}
