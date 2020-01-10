<?php

namespace Syno\Storm\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Syno\Storm\Services\PageRequest;
use Syno\Storm\Services\SurveyRequest;

class PageListener implements EventSubscriberInterface
{
    /** @var PageRequest */
    private $pageRequestService;

    /** @var SurveyRequest */
    private $surveyRequestService;

    /** @var RouterInterface */
    private $router;

    /**
     * @param PageRequest     $pageRequestService
     * @param SurveyRequest   $surveyRequestService
     * @param RouterInterface $router
     */
    public function __construct(PageRequest $pageRequestService, SurveyRequest $surveyRequestService, RouterInterface $router)
    {
        $this->pageRequestService   = $pageRequestService;
        $this->surveyRequestService = $surveyRequestService;
        $this->router               = $router;
    }


    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        /** @var Request $request */
        $request = $event->getRequest();

        if (!$this->pageRequestService->hasPageId($request)) {
            return;
        }

        if (!$this->surveyRequestService->hasSurvey($request)) {
            throw new \UnexpectedValueException('Survey attribute is not set');
        }

        $pageId = $this->pageRequestService->getPageId($request);
        if ($pageId) {
            $survey = $this->surveyRequestService->getSurvey($request);
            $page = $survey->getPage($pageId);
            if ($page) {
                $this->pageRequestService->setPage($request, $page);
                return;
            }
        }

        $event->setResponse(new RedirectResponse($this->router->generate('page.unavailable')));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 7],
        ];
    }
}
