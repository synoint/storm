<?php

namespace Syno\Storm\Services;

use Syno\Storm\Document;

class Translation
{
    public function setPageLocale(Document\Page $page, string $locale)
    {
        $page->setCurrentLocale($locale);

        foreach ($page->getQuestions() as $question) {
            $question->setCurrentLocale($locale);

            foreach ($question->getAnswers() as $answer) {
                $answer->setCurrentLocale($locale);
            }
        }
    }
}
