<?php

namespace Syno\Storm\Services;

use Syno\Storm\Document;

class SurveyEndPage
{
    public function getEndPageContentByLocale(Document\Survey $survey, string $locale, string $type): ?string
    {
        /** @var Document\SurveyEndPage $endPage */
        foreach ($survey->getEndPages() as $endPage) {
            if($endPage->getLanguage() == $locale && $endPage->getType() == $type) {
                return $endPage->getContent();
            }
        }

        return null;
    }
}
