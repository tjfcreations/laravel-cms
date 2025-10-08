<?php

namespace Feenstra\CMS\Pagebuilder\Shortcodes;

use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Parser\RegularParser;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;
use Feenstra\CMS\Pagebuilder\Registry;
use Feenstra\CMS\Pagebuilder\Support\PageRenderer;

class ShortcodeProcessor {
    protected PageRenderer $pageRenderer;
    protected Processor $processor;

    protected array $additionalData = [];

    public function __construct(PageRenderer $pageRenderer) {
        $this->pageRenderer = $pageRenderer;

        $this->init();
    }

    public function init() {
        $handlers = new HandlerContainer();

        foreach (Registry::shortcodes() as $shortcode) {
            $handlers->add($shortcode::$name, function (ShortcodeInterface $s) use ($shortcode) {
                $arguments = collect($s->getParameters());
                return $shortcode->resolve($arguments, $this->getData(), $this);
            });
        }

        $this->processor = new Processor(new RegularParser(), $handlers);
    }

    public function getData() {
        return array_merge($this->pageRenderer->getData(), $this->additionalData);
    }

    public function process(string $text, array $data = []) {
        $this->additionalData = $data;
        $result = $this->processor->process($text);
        $this->additionalData = [];

        return $result;
    }
}
