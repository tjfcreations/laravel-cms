<?php
namespace Feenstra\CMS\I18n\Support;

/**
 * Helper class to escape certain parts of a text (like HTML tags) before sending it to a translation service,
 * and unescape them back to their original form afterwards.
 */
class PatternEscaper {
    public string $text;
    public array $replacements;

    public function __construct(string $text) {
        $this->text = $text;
        $this->replacements = [];
    }

    /**
     * Replace all occurrences of the given pattern in the text with placeholders.
     */
    public function escape(string $pattern): string {
        return preg_replace_callback($pattern, function($matches) {
            // Create a unique incrementing placeholder that looks like an HTML tag, because those
            // are not translated (see https://cloud.google.com/translate/faq#technical_questions).
            $replacement = '&lt;__'.count($this->replacements).'__&gt;';
            $this->replacements[$replacement] = $matches[0];

            return $replacement;
        }, $this->text);
    }

    /**
     * Revert the placeholders back to their original values.
     */
    public function unescape(string $text): string {
        return str_replace(array_keys($this->replacements), array_values($this->replacements), $text);
    }
}