<?php
    namespace Feenstra\CMS\Pagebuilder\Filament\Forms\Components;

    use Filament\Forms\Components\Builder;
    use Feenstra\CMS\Pagebuilder\Support\Block;
    use Filament\Forms\Form;
    use Feenstra\CMS\Pagebuilder\Registry;

    class Pagebuilder extends Builder {
        protected function setUp(): void {
            parent::setUp();

            $this
                ->hiddenLabel()
                ->addActionLabel('Blok toevoegen')
                ->addActionAlignment('left')
                ->blocks(function() {
                    $builderBlocks = [];

                    foreach(Registry::blocks() as $block) {
                        $builderBlocks[] = Builder\Block::make($block->getType())
                            ->label($block::$label)
                            ->icon($block::$icon)
                            ->schema(function() use ($block) {
                                return $block->getSchema();
                            });
                    }

                    return $builderBlocks;
                });
        }
    }