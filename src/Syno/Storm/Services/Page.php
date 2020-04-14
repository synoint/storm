<?php

namespace Syno\Storm\Services;


use Symfony\Contracts\Translation\TranslatorInterface;
use Syno\Storm\Document;
use Syno\Storm\Services;

class Page
{
    /** @var Services\Condition */
    private $conditionService;

    /** @var Services\Survey */
    private $surveyService;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * Page constructor.
     *
     * @param Condition           $conditionService
     * @param Survey              $surveyService
     * @param TranslatorInterface $translator
     */
    public function __construct(Condition $conditionService, Survey $surveyService, TranslatorInterface $translator)
    {
        $this->conditionService = $conditionService;
        $this->surveyService    = $surveyService;
        $this->translator       = $translator;
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

    /**
     * @param Document\Survey $survey
     * @param Document\Page   $page
     *
     * @return string
     */
    public function getPrefix(Document\Survey $survey, Document\Page $page): string
    {
        if ($survey->isFirstPage($page->getPageId())) {
            return $this->translator->trans('survey.title.first_questions');
        } else {
            if ($page->getQuestions()->count() > 1) {
                $progress = $this->surveyService->getProgress($survey, $page);
                $text     = '';
                if ($progress >= 100) {
                    $text = $this->translator->trans('survey.thank_you');
                } elseif ($progress >= 80) {
                    $text = $this->translator->trans('survey.title.almost_done:');
                } elseif ($progress >= 60) {
                    $text = $this->translator->trans('survey.title.2_3_completed');
                } elseif ($progress >= 50) {
                    $text = $this->translator->trans('survey.title.1_1_completed');
                } elseif ($progress >= 25) {
                    $text = $this->translator->trans('survey.title.1_3_completed');
                }

                return $text;
            } else {
                return $this->translator->trans('survey.title.first_question');
            }
        }
    }
}
