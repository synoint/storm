<?php

namespace Syno\Storm\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Syno\Storm\Services\SurveySession;

class SurveySessionListener
{
    /** @var SurveySession */
    private $surveySession;

    /**
     * @param SurveySession $surveySession
     */
    public function __construct(SurveySession $surveySession)
    {
        $this->surveySession = $surveySession;
    }

    public function onKernelControllerArguments(ControllerArgumentsEvent $event)
    {
        /** @var Request $request */
        $request = $event->getRequest();
        $arguments = $event->getArguments();

        if ($request->hasPreviousSession() && in_array('surveyId', $arguments)) {
            $this->surveySession->init($arguments['surveyId']);
        }
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if ($event->getRequest()->hasPreviousSession()) {
            $this->surveySession->save();
        }
    }
}
