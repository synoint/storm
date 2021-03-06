<?php

namespace Syno\Storm\Services;

use Syno\Storm\Document;
use Syno\Storm\Services;

class Page
{
    /** @var Services\Condition */
    private $conditionService;

    /**
     * Page constructor.
     *
     * @param Condition $conditionService
     */
    public function __construct(Condition $conditionService)
    {
        $this->conditionService = $conditionService;
    }

    /**
     * @param Document\Survey   $survey
     * @param Document\Page     $page
     * @param Document\Response $response
     *
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
