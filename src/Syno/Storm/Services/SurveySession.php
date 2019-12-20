<?php

namespace Syno\Storm\Services;

use Symfony\Component\HttpFoundation\RequestStack;

class SurveySession
{
    /** @var RequestStack */
    private $requestStack;

    /** @var SurveySessionBag */
    private $surveySessionBag;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function init(int $surveyId)
    {
        $this->surveySessionBag = $this->getBag($surveyId);
    }

    public function save()
    {
        if (null !== $this->surveySessionBag) {
            $this->requestStack->getMasterRequest()->getSession()->set(
                $this->getKey($this->surveySessionBag->surveyId),
                $this->surveySessionBag
            );
        }
    }

    public function startSession(int $surveyId, string $mode)
    {
        $request = $this->requestStack->getMasterRequest();
        if ($request->hasPreviousSession()) {
            $request->getSession()->migrate();
        }

        $this->surveySessionBag           = new SurveySessionBag();
        $this->surveySessionBag->surveyId = $surveyId;
        $this->surveySessionBag->mode     = $mode;
    }

    /**
     * @param int $surveyId
     *
     * @return mixed
     */
    private function getBag(int $surveyId)
    {
        return $this->requestStack->getMasterRequest()->getSession()->get($this->getKey($surveyId));
    }

    /**
     * @param int $surveyId
     *
     * @return string
     */
    private function getKey(int $surveyId)
    {
        return sprintf('survey_%d', $surveyId);
    }


}
