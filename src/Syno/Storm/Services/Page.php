<?php

namespace Syno\Storm\Services;

use Syno\Storm\Document;
use Syno\Storm\Services;

class Page
{
    /** @var Services\Condition */
    private $conditionService;

    /**
     * @param Services\Condition $conditionService
     */
    public function __construct(Services\Condition $conditionService)
    {
        $this->conditionService = $conditionService;
    }

    /**
     * @param Document\Survey   $survey
     * @param Document\Page     $page
     * @param Document\Response $responses
     *
     * @return null|Document\Page
     */
    public function getNextPage(Document\Survey $survey, Document\Page $page, Document\Response $responses):? object
    {
        $nextPage = $survey->getNextPage($page->getPageId());
        if (!empty($nextPage)) {
            if (empty($this->conditionService->filterQuestionsByShowCondition($nextPage->getQuestions(), $responses)->count())) {
                $nextPage = $this->getNextPage($survey, $nextPage, $responses);
            }
        }

        return $nextPage;
    }
}