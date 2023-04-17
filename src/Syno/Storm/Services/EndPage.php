<?php

namespace Syno\Storm\Services;

use Syno\Storm\Document;

class EndPage
{
    public function getEndPageContentByLocale(Document\Survey $survey, string $locale, string $type): ?string
    {
        /** @var Document\EndPage $endPage */
        foreach ($survey->getEndPages() as $endPage) {
            if($endPage->getLanguage() == $locale && $endPage->getType() == $type) {
                return $endPage->getContent();
            }
        }

        return null;
    }
}
