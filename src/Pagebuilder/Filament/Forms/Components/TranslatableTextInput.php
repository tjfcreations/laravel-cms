<?php

namespace Feenstra\CMS\Pagebuilder\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Actions\Action;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Form;
use Feenstra\CMS\I18n\Filament\Support\TranslationsForm;
use Feenstra\CMS\I18n\Interfaces\TranslatableInterface;
use Filament\Forms\Components\Tabs;
use Feenstra\CMS\I18n\Models\Locale;
use Feenstra\CMS\I18n\Models\Translation;

class TranslatableTextInput extends TextInput {
    protected function setUp(): void {
        parent::setUp();

        $this->suffixAction(
            Action::make('translate')
                ->color(Color::Emerald)
                ->label('Vertalen')
                ->icon('heroicon-s-chat-bubble-left-right')
                ->form(function (Form $form) {
                    return $this->getTranslateForm($form);
                })
        );
    }

    protected function getTranslateForm(Form $form): Form {
        return $form->schema([
            TranslationsForm::makeTabs(function (Locale $locale) use ($form) {
                return $this->makeTab($locale, $form);
            }, Locale::allWithDefaultLast())
        ]);
    }

    protected function makeTab(Locale $locale, Form $form) {
        return Tabs\Tab::make($locale->name)
            ->schema(function (TranslatableInterface $record, $get) use ($locale, $form) {
                $component = TextInput::make($this->getName())
                    ->label($this->getLabel());

                return [
                    TranslationsForm::mutateFormComponent($locale, $component)
                ];
            });
    }
}
