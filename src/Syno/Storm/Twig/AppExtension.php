<?php

namespace Syno\Storm\Twig;

use Syno\Storm\Document\Page;
use Syno\Storm\Document\Survey;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Syno\Storm\Services;

class AppExtension extends AbstractExtension
{
    private Services\Survey $surveyService;

    public function __construct(Services\Survey $surveyService)
    {
        $this->surveyService = $surveyService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'survey_progress', function (Survey $survey, Page $currentPage) {
                echo $this->surveyService->getProgress($survey, $currentPage);
            }),
            new TwigFunction(
                'page_prefix', function (Survey $survey, Page $currentPage) {
                return $this->getPagePrefix($survey, $currentPage);
            })
        ];
    }

    public function getPagePrefix(Survey $survey, Page $page): string
    {
        if ($survey->isFirstPage($page->getPageId())) {
            if ($page->getQuestions()->count() > 1) {
                return 'survey.title.first_questions';
            }
            return 'survey.title.first_question';
        }

        $progress = $this->surveyService->getProgress($survey, $page);
        $text     = 'survey.title.default';
        if ($progress >= 100) {
            $text = 'survey.thank_you';
        } elseif ($progress >= 80) {
            $text = 'survey.title.almost_done';
        } elseif ($progress >= 60) {
            $text = 'survey.title.2_3_completed';
        } elseif ($progress >= 50) {
            $text = 'survey.title.1_2_completed';
        } elseif ($progress >= 25) {
            $text = 'survey.title.1_3_completed';
        }

        return $text;
    }
}
