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
                'survey_progress', function (Document\Response $response, Document\Survey $survey) {
                return $this->getProgress($response, $survey);
            }),
            new TwigFunction(
                'page_prefix', function (Document\Response $response, Document\Survey $survey) {
                return $this->getPagePrefix($response, $survey);
            })
        ];
    }

    public function getProgress(Document\Response $response, Document\Survey $survey): int
    {
        return $this->surveyService->getProgress($response, $survey);
    }

    public function getPagePrefix(Document\Response $response, Document\Survey $survey): string
    {
        $progress = $this->surveyService->getProgress($response, $survey);

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
