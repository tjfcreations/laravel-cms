<?php
    namespace Tjall\Pagebuilder;

    use Thunder\Shortcode\HandlerContainer\HandlerContainer;
    use Thunder\Shortcode\Parser\RegularParser;
    use Thunder\Shortcode\Processor\Processor;
    use Thunder\Shortcode\Shortcode\ShortcodeInterface;

    class ShortcodeProcessor {
        protected static Processor $processor;
        protected static array $currentData;

        public static function init() {
            $handlers = new HandlerContainer();

            foreach(Registry::shortcodes() as $shortcode) {
                $handlers->add($shortcode::$name, function(ShortcodeInterface $s) use($shortcode) {
                    $arguments = collect($s->getParameters());
                    return $shortcode->resolve($arguments, self::$currentData);
                });
            }

            self::$processor = new Processor(new RegularParser(), $handlers);
        }

        public static function resolve(string $text, array $data) {
            self::$currentData = $data;
            return self::$processor->process($text);
        }
    }