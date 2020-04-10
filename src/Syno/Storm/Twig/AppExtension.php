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

    /** @var Services\Page */
    private $pageService;

    /**
     * AppExtension constructor.
     *
     * @param Services\Survey $surveyService
     * @param Services\Page   $pageService
     */
    public function __construct(Services\Survey $surveyService, Services\Page $pageService)
    {
        $this->surveyService = $surveyService;
        $this->pageService   = $pageService;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_survey_progress', function (Survey $survey, Page $currentPage) {
                echo $this->surveyService->getProgress($survey, $currentPage);
            }),
            new TwigFunction('get_page_prefix', function (Survey $survey, Page $currentPage) {
                echo $this->pageService->getPrefix($survey, $currentPage);
            })
        ];
    }
}
