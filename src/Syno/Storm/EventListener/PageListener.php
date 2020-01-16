<?php

namespace Syno\Storm\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Syno\Storm\RequestHandler\Page;
use Syno\Storm\RequestHandler\Survey;

class PageListener implements EventSubscriberInterface
{
    /** @var Page */
    private $pageRequestHandler;

    /** @var Survey */
    private $surveyRequestHandler;

    /** @var RouterInterface */
    private $router;

    /**
     * @param Page            $pageRequestHandler
     * @param Survey          $surveyRequestHandler
     * @param RouterInterface $router
     */
    public function __construct(Page $pageRequestHandler, Survey $surveyRequestHandler, RouterInterface $router)
    {
        $this->pageRequestHandler   = $pageRequestHandler;
        $this->surveyRequestHandler = $surveyRequestHandler;
        $this->router               = $router;
    }


    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        /** @var Request $request */
        $request = $event->getRequest();

        if (!$this->pageRequestHandler->hasPageId($request)) {
            return;
        }

        if (!$this->surveyRequestHandler->hasSurvey($request)) {
            throw new \UnexpectedValueException('Survey attribute is not set');
        }

        $pageId = $this->pageRequestHandler->getPageId($request);
        if ($pageId) {
            $survey = $this->surveyRequestHandler->getSurvey($request);
            $page = $survey->getPage($pageId);
            if ($page) {
                $this->pageRequestHandler->setPage($request, $page);
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
