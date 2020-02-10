<?php

namespace Syno\Storm\Twig;

use Syno\Storm\Document\Page;
use Syno\Storm\Document\Survey;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Syno\Storm\Services;

class AppExtension extends AbstractExtension
{
    /** @var Services\Survey */
    private $surveyService;

    public function __construct(Services\Survey $surveyService)
    {
        $this->surveyService = $surveyService;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_survey_progress', function (Survey $survey, Page $currentPage) {

                echo $this->surveyService->getProgress($survey, $currentPage);
            })
        ];
    }
}
