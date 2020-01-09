<?php

namespace Syno\Storm\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Syno\Storm\Document;

class PageListener implements EventSubscriberInterface
{
    const ATTR = 'page';

    /** @var RouterInterface */
    private $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }


    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        /** @var Request $request */
        $request = $event->getRequest();

        if (!$request->attributes->has('pageId')) {
            return;
        }

        if (!$request->attributes->has(SurveyListener::ATTR)) {
            throw new \UnexpectedValueException('Survey attribute is not set');
        }

        $page = null;
        $pageId = $request->attributes->getInt('pageId');
        if ($pageId) {
            $survey = $request->attributes->get(SurveyListener::ATTR);
            if ($survey instanceof Document\Survey) {
                $page = $survey->getPage($pageId);
                if ($page) {
                    $request->attributes->set(self::ATTR, $page);
                    return;
                }
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
