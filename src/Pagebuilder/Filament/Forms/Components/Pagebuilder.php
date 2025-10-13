<?php

namespace Feenstra\CMS\Pagebuilder\Filament\Forms\Components;

use Filament\Forms\Components\Builder;
use Feenstra\CMS\Pagebuilder\Support\Block;
use Filament\Forms\Form;
use Feenstra\CMS\Pagebuilder\Registry;
use Filament\Forms\Components\Actions\Action;

class Pagebuilder extends Builder {
    protected function setUp(): void {
        parent::setUp();

        $this
            ->hiddenLabel()
            ->addActionLabel('Blok toevoegen')
            ->addActionAlignment('left')
            ->extraItemActions([
                Action::make('show_edit')
                    ->label('Bewerken')
                    ->icon('heroicon-s-pencil-square')
                    ->color('primary')
                    ->visible(function (array $arguments) {
                        $block = $this->getPagebuilderBlock($arguments['item']);

                        // use temporary form to check if the form has component
                        $form = new Form($this->getLivewire());
                        return count($block->getForm($form)->getComponents()) > 0;
                    })
                    ->form(function (Form $form, array $arguments) {
                        $block = $this->getPagebuilderBlock($arguments['item']);
                        return $block->getForm($form);
                    })
                // Action::make('duplicate')
                //     ->label('Dupliceren')
                //     ->icon('heroicon-s-square-2-stack')
                //     ->action(function (array $data, ?string $itemKey, Form $form) {
                //         $this->duplicateItem($itemKey);
                //     }),
            ])
            ->blocks(function () {
                $builderBlocks = [];

                foreach (Registry::blocks() as $block) {
                    $builderBlocks[] = Builder\Block::make($block->getType())
                        ->label($block::$label)
                        ->icon($block::$icon)
                        ->schema(function () use ($block) {
                            return $block->getSchema();
                        });
                }

                return $builderBlocks;
            });
    }

    protected function getPagebuilderBlock(string $uuid): Block {
        $state = $this->getState();
        $item = $state[$uuid] ?? null;
        $block = Block::findByType($item['type'] ?? null);
        if (!($block instanceof Block)) {
            throw new \Exception("Block of type '{$item['type']}' not found.");
        }

        return $block;
    }
}
