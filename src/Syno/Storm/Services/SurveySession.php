<?php

namespace Syno\Storm\Services;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SurveySession
{
    const COMPLETE_GRANTED_KEY = 'complete_granted';

    /** @var RequestStack */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function grantComplete(int $surveyId)
    {
        $this->getSession()->set(self::COMPLETE_GRANTED_KEY, $surveyId);
    }

    /**
     * @param int $surveyId
     *
     * @return bool
     */
    public function isCompleteGranted(int $surveyId)
    {
        return $surveyId === (int) $this->getSession()->remove(self::COMPLETE_GRANTED_KEY);
    }

    /**
     * @return SessionInterface
     */
    private function getSession()
    {
        return $this->requestStack->getMasterRequest()->getSession();
    }


}
