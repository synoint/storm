<?php

namespace Syno\Storm\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Syno\Storm\Plugin\PluginManager;
use Syno\Storm\RequestHandler\Response;
use Syno\Storm\RequestHandler\Survey;
use Syno\Storm\Traits\RouteAware;

class PluginSubscriber implements EventSubscriberInterface
{
    use RouteAware;

    private PluginManager $pluginManager;
    private Response      $responseHandler;
    private Survey        $surveyHandler;

    public function __construct(
        PluginManager $pluginManager,
        Response $responseHandler,
        Survey $surveyHandler
    )
    {
        $this->pluginManager   = $pluginManager;
        $this->responseHandler = $responseHandler;
        $this->surveyHandler   = $surveyHandler;
    }


    public function executeOnRequest(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->surveyHandler->hasSurvey()) {
            return;
        }

        if (!$this->responseHandler->hasResponse()) {
            return;
        }

        $survey = $this->surveyHandler->getSurvey();
        $plugin = $this->pluginManager->getActivePlugin($survey->getSurveyId());

        if (!$plugin) {
            return;
        }

        if ($this->isSurveyEntrance($event->getRequest())) {
            $plugin->onSurveyEntry($event->getRequest());
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => 'executeOnRequest'];
    }
}
