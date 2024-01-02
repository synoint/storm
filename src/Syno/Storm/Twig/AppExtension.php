<?php

namespace Syno\Storm\Twig;

use Syno\Storm\Document;
use Syno\Storm\Services;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private Services\Page $pageService;

    private ?float $progress = null;

    public function __construct(Services\Page $pageService)
    {
        $this->pageService = $pageService;
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
            }),
            new TwigFunction(
                'survey_pages_for_debug', function (Document\Survey $survey) {
                return $this->getSurveyPagesForDebug($survey);
            })
        ];
    }

    public function getProgress(Document\Response $response, Document\Survey $survey): int
    {
        if (null === $this->progress) {
            $this->progress = 0;
            $answered = $response->getNumberOfAnsweredQuestions();
            if ($answered) {
                $total = $this->pageService->getTotalQuestions($survey->getSurveyId(), $survey->getVersion());
                $this->progress = ($total) ? round(($answered / $total) * 100) : 0;
            }
        }

        return $this->progress;
    }

    public function getPagePrefix(Document\Response $response, Document\Survey $survey): string
    {
        $progress = $this->getProgress($response, $survey);

        if ($progress == 100) {
            $text = 'survey.thank_you';
        } elseif ($progress == 0) {
            $text = 'survey.title.first_question';
        } else {
            $text = 'survey.title.complete_progress';
        }

        return $text;
    }

    public function getSurveyPagesForDebug(Document\Survey $survey): array
    {
        return $this->pageService->findAllForDebug($survey->getSurveyId(), $survey->getVersion());
    }
}
