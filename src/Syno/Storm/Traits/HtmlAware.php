<?php

namespace Syno\Storm\Traits;

trait HtmlAware
{
    private function cleanMarkup(string $text): string
    {
        // replace all "<" that are not parts of html with "&lt;" so they are not removed by strip_tags
        $text = preg_replace("/<([^>]*(<|$))/", "&lt;$1", $text);
        $text = strip_tags($text);
        // replace "&lt;" back to "<" and decode other chars
        $text = html_entity_decode($text);
        // replace multiple spaces with a single
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }
}
