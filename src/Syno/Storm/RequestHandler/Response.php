<?php

namespace Syno\Storm\RequestHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\RequestStack;
use Syno\Storm\Document;
use Syno\Storm\Services;

class Response
{
    const ATTR = 'response';

    private RequestStack      $requestStack;
    private ResponseId        $responseId;
    private Services\Response $responseService;

    public function __construct(
        RequestStack $requestStack,
        ResponseId $responseId,
        Services\Response $responseService
    ) {
        $this->requestStack    = $requestStack;
        $this->responseId      = $responseId;
        $this->responseService = $responseService;
    }

    public function getResponse(): Document\Response
    {
        $response = $this->requestStack->getCurrentRequest()->attributes->get(self::ATTR);

        if (!$response instanceof Document\Response) {
            throw new \UnexpectedValueException('Response attribute is invalid');
        }

        return $response;
    }

    public function setResponse(Document\Response $response)
    {
        $this->requestStack->getCurrentRequest()->attributes->set(self::ATTR, $response);
    }

    public function hasResponse(): bool
    {
        return $this->requestStack->getCurrentRequest()->attributes->has(self::ATTR);
    }

    public function clearResponse()
    {
        $this->responseId->clear($this->getResponse()->getSurveyId());
        $this->requestStack->getCurrentRequest()->attributes->remove(self::ATTR);
    }

    public function saveResponse(Document\Response $response, bool $initialSave = false)
    {
        $this->responseService->save($response);
        $this->setResponse($response);

        if ($initialSave) {
            $this->responseId->set($response);
        }
    }

    public function getSaved(int $surveyId): ?Document\Response
    {
        $responseId = $this->responseId->get($surveyId);

        return $responseId ? $this->responseService->findBySurveyIdAndResponseId($surveyId, $responseId) : null;
    }

    public function getNew(Document\Survey $survey): Document\Response
    {
        $responseId = $this->responseId->get($survey->getSurveyId());
        $result     = $this->responseService->getNew($responseId);
        $result
            ->setSurveyId($survey->getSurveyId())
            ->setSurveyVersion($survey->getVersion())
            ->setMode(
                $this->responseService->getModeByRoute(
                    $this->requestStack->getCurrentRequest()->attributes->get('_route')
                )
            )
            ->setLocale($this->requestStack->getCurrentRequest()->attributes->get('_locale'));

        return $result;
    }

    public function extractParameters(Collection $surveyParameters): Collection
    {
        $result = new ArrayCollection();
        /** @var Document\Parameter $surveyParameter */
        foreach ($surveyParameters as $surveyParameter) {
            if ($this->requestStack->getCurrentRequest()->query->has($surveyParameter->getUrlParam())) {

                if (!is_array($this->requestStack->getCurrentRequest()->query->get($surveyParameter->getUrlParam()))) {
                    $value = clone $surveyParameter;
                    $value->setValue($this->requestStack->getCurrentRequest()->query->get($value->getUrlParam()));
                    $result[] = $value;
                }
            }
        }

        return $result;
    }

    public function addUserAgent(Document\Response $response)
    {
        $response->addUserAgent(
            $this->requestStack->getCurrentRequest()->getClientIp(),
            $this->requestStack->getCurrentRequest()->headers->get('User-Agent', '')
        );
        $this->responseService->save($response);
    }

    public function hasModeChanged(string $surveyMode): bool
    {
        return $surveyMode !== $this->responseService->getModeByRoute(
                $this->requestStack->getCurrentRequest()->attributes->get('_route')
            );
    }
}
