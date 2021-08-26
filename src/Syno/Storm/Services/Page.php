<?php

namespace Syno\Storm\Services;

use Syno\Storm\Document;

class Page
{
    private Condition $conditionService;

    public function __construct(Condition $conditionService)
    {
        $this->conditionService = $conditionService;
    }

    /**
     * @return null|Document\Page
     */
    public function getNextPage(Document\Survey $survey, Document\Page $page, Document\Response $response): ?object
    {
        $nextPage = $survey->getNextPage($page->getPageId());
        if (!empty($nextPage) && !$nextPage->getQuestions()->isEmpty()) {
            if (empty($this->conditionService->filterQuestionsByShowCondition($nextPage->getQuestions(), $response)->count())) {
                $nextPage = $this->getNextPage($survey, $nextPage, $response);
            }
        }

        return $nextPage;
    }

}
