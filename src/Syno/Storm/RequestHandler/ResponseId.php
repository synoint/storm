<?php

namespace Syno\Storm\RequestHandler;

use Symfony\Component\HttpFoundation\RequestStack;
use Syno\Storm\Document\Response;


class ResponseId
{
    public const ROUTER_RESPONDENT_COOKIE = 'respondent';

    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function get(int $surveyId): ?string
    {
        $result = $this->requestStack->getCurrentRequest()->query->get('id');
        if (!$result) {
            $result = $this->requestStack->getCurrentRequest()->getSession()->get('id' . $surveyId);
        }
        if (!$result) {
            $result = $this->requestStack->getCurrentRequest()->cookies->get(self::ROUTER_RESPONDENT_COOKIE);
        }

        if (null !== $result) {
            if (!is_string($result)) {
                $result = null;
            } else {
                $result = trim($result);
                if (preg_match('/[^a-zA-Z0-9_\-]/', $result)) {
                    $result = null;
                }
            }
        }

        return $result;
    }

    public function set(Response $response)
    {
        $this->requestStack->getCurrentRequest()->getSession()->set(
            'id' . $response->getSurveyId(), $response->getResponseId()
        );
    }

    public function clear(int $surveyId)
    {
        $this->requestStack->getCurrentRequest()->getSession()->remove('id' . $surveyId);
        $this->requestStack->getCurrentRequest()->getSession()->migrate(true);
    }
}
