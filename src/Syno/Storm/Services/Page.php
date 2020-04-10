<?php

namespace Syno\Storm\Services;

use Syno\Storm\Document;
use Syno\Storm\Services;

class Page
{
    /** @var Services\Condition */
    private $conditionService;

    /** @var Services\Survey */
    private $surveyService;

    /**
     * Page constructor.
     *
     * @param Condition $conditionService
     * @param Survey    $surveyService
     */
    public function __construct(Condition $conditionService, Survey $surveyService)
    {
        $this->conditionService = $conditionService;
        $this->surveyService    = $surveyService;
    }

    /**
     * @param Document\Survey   $survey
     * @param Document\Page     $page
     * @param Document\Response $response
     *
     * @return null|Document\Page
     */
    public function getNextPage(Document\Survey $survey, Document\Page $page, Document\Response $response):? object
    {
        $nextPage = $survey->getNextPage($page->getPageId());
        if (!empty($nextPage) && !$nextPage->getQuestions()->isEmpty()) {
            if (empty($this->conditionService->filterQuestionsByShowCondition($nextPage->getQuestions(), $response)->count())) {
                $nextPage = $this->getNextPage($survey, $nextPage, $response);
            }
        }

        return $nextPage;
    }

    public function getPrefix(Document\Survey $survey, Document\Page $page): string
    {
        if ($survey->isFirstPage($page->getPageId())) {
            return 'Welcome - First Questions';
        } else {
            if ($page->getQuestions()->count() > 1) {
                $progress = $this->surveyService->getProgress($survey, $page);
                $text     = '';
                if ($progress >= 100) {
                    $text = 'Thank you';
                } elseif ($progress >= 80) {
                    $text = 'Almost done';
                } elseif ($progress >= 60) {
                    $text = '2/3 completed';
                } elseif ($progress >= 50) {
                    $text = '1/2 completed';
                } elseif ($progress >= 25) {
                    $text = '1/3 completed';
                }
                return $text;
            } else {
                return 'Welcome - First Question';
            }
        }
    }
}
