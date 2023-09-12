<?php

namespace Syno\Storm\Twig;

use Syno\Storm\Document;
use Syno\Storm\Services;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

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
                'survey_progress', function (Document\Survey $survey, Document\PageInterface $currentPage) {
                return $this->getProgress($survey, $currentPage);
            }),
            new TwigFunction(
                'page_prefix', function (Document\Survey $survey, Document\PageInterface $currentPage) {
                return $this->getPagePrefix($survey, $currentPage);
            })
        ];
    }

    public function getProgress(Document\Survey $survey, Document\PageInterface $page): int
    {
        return $this->surveyService->getProgress($survey, $page);
    }

    public function getPagePrefix(Document\Survey $survey, Document\PageInterface $page): string
    {
        $progress = $this->surveyService->getProgress($survey, $page);

        if ($progress == 100) {
            $text = 'survey.thank_you';
        } elseif ($progress == 0) {
            $text = 'survey.title.first_question';
        } else {
            $text = 'survey.title.complete_progress';
        }

        return $text;
    }
}
