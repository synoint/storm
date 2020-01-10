<?php

namespace Syno\Storm\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Syno\Storm\Document;
use Syno\Storm\Event\SurveyCompleted;
use Syno\Storm\Services\Response;
use Syno\Storm\Services\Survey;


class ResponseListener implements EventSubscriberInterface
{
    CONST ATTR = 'response';

    /** @var Response */
    private $responseService;
    /** @var Survey */
    private $surveyService;
    /** @var RouterInterface */
    private $router;

    /**
     * @param Response        $responseService
     * @param Survey          $surveyService
     * @param RouterInterface $router
     */
    public function __construct(Response $responseService, Survey $surveyService, RouterInterface $router)
    {
        $this->responseService = $responseService;
        $this->surveyService   = $surveyService;
        $this->router          = $router;
    }


    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        /** @var Request $request */
        $request = $event->getRequest();

        if (!$request->attributes->has(SurveyListener::ATTR)) {
            return;
        }

        $survey = $request->attributes->get(SurveyListener::ATTR);
        if (!$survey instanceof Document\Survey) {
            throw new \UnexpectedValueException('Survey attribute is invalid');
        }

        $responseId = $this->getResponseId($request, $survey->getSurveyId());
        if (!empty($responseId)) {
            $surveyResponse = $this->responseService->findBySurveyIdAndResponseId($survey->getSurveyId(), $responseId);
            if ($surveyResponse) {

                if ($surveyResponse->isCompleted()) {
                    $event->setResponse(new RedirectResponse($this->router->generate('survey.complete')));
                    return;
                }

                /**
                 * If there's a mismatch between the current survey version and the one that was used before,
                 * let's replace the current version of survey in the request with the previously started one
                 */
                if ($surveyResponse->getSurveyVersion() !== $survey->getVersion()) {
                    $previousSurvey = $this->surveyService->findBySurveyIdAndVersion(
                        $surveyResponse->getSurveyId(),
                        $surveyResponse->getSurveyVersion()
                    );
                    if (!$previousSurvey) {
                        $event->setResponse(new RedirectResponse($this->router->generate('survey.unavailable')));
                        return;
                    }
                    $request->attributes->set(SurveyListener::ATTR, $previousSurvey);
                    $survey = $previousSurvey;
                }

                /**
                 * Resume survey
                 */
                if ($this->isSurveyEntrance($request) &&
                    !empty($surveyResponse->getPageId()) &&
                    null !== $survey->getPage($surveyResponse->getPageId())
                ) {
                    $event->setResponse(
                        new RedirectResponse(
                            $this->router->generate('page.index', [
                                'surveyId' => $survey->getSurveyId(),
                                'pageId'   => $surveyResponse->getPageId()
                            ])
                        )
                    );
                }

                $request->attributes->set(self::ATTR, $surveyResponse);
                return;
            }
        }

        /**
         * New response
         */
        if (!$this->isSurveyEntrance($request)) {
            $event->setResponse(
                new RedirectResponse(
                    $this->router->generate(
                        'survey.index',
                        array_merge(
                            ['surveyId' => $survey->getSurveyId()],
                            $request->query->all()
                        )
                    )
                )
            );
            return;
        }

        $surveyResponse = $this->responseService->getNew($responseId);
        $surveyResponse
            ->setSurveyId($survey->getSurveyId())
            ->setSurveyVersion($survey->getVersion())
            ->setMode(
                $this->responseService->getMode($request->attributes->get('_route'))
            )
            ->setLocale($request->attributes->get('_locale'));

        /** @var Document\HiddenValue $surveyValue */
        foreach ($survey->getHiddenValues() as $surveyValue) {
            if ($request->query->has($surveyValue->urlParam)) {
                $responseValue = clone $surveyValue;
                if ($responseValue->type === Document\HiddenValue::TYPE_INT) {
                    $responseValue->value = $request->query->getInt($responseValue->urlParam);
                } else {
                    $responseValue->value = $request->query->get($responseValue->urlParam);
                }
                $surveyResponse->addHiddenValue($responseValue);
            }
        }

        $request->attributes->set(self::ATTR, $surveyResponse);
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        /** @var Request $request */
        $request = $event->getRequest();

        if (!$request->attributes->has(self::ATTR)) {
            return;
        }

        /** @var Document\Response $surveyResponse */
        $surveyResponse = $request->attributes->get(self::ATTR);
        if (!$surveyResponse instanceof Document\Response) {
            throw new \UnexpectedValueException('Invalid survey response object');
        }

        if ($surveyResponse->isCompleted()) {
            $request->attributes->remove(self::ATTR);
            $request->getSession()->remove('id:' . $surveyResponse->getSurveyId());
            $event->getResponse()->headers->clearCookie('id:'. $surveyResponse->getSurveyId());
            return;
        }

        if ($request->attributes->has(PageListener::ATTR)) {
            /** @var Document\Page $page */
            $page = $request->attributes->get(PageListener::ATTR);
            $surveyResponse->setPageId($page->getPageId());
        }

        $surveyResponse->addUserAgent(
            IPUtils::anonymize($request->getClientIp()),
            $request->headers->get('User-Agent')
        );

        $this->responseService->save($surveyResponse);

        if ($surveyResponse->getResponseId() !== $request->getSession()->get('id:' . $surveyResponse->getSurveyId())) {
            $request->getSession()->set('id:' . $surveyResponse->getSurveyId(), $surveyResponse->getResponseId());
        }

        if ($surveyResponse->getResponseId() !== $request->cookies->get('id:' . $surveyResponse->getSurveyId())) {
            $event->getResponse()->headers->setCookie(
                new Cookie(
                    'id:' . $surveyResponse->getSurveyId(),
                    $surveyResponse->getResponseId(),
                    time() + 3600
                )
            );
        }

    }

    /**
     * @param SurveyCompleted $event
     */
    public function onSurveyCompleted(SurveyCompleted $event)
    {
        /** @var Request $request */
        $request = $event->getRequest();

        if (!$request->attributes->has(self::ATTR)) {
            return;
        }

        $surveyResponse = $request->attributes->get(self::ATTR);
        if (!$surveyResponse instanceof Document\Response) {
            throw new \UnexpectedValueException('Invalid survey response object');
        }

        $surveyResponse->setCompleted(true);
        $this->responseService->save($surveyResponse);
    }



    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 4],
            KernelEvents::RESPONSE => 'onKernelResponse',
            SurveyCompleted::class => 'onSurveyCompleted'
        ];
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function isSurveyEntrance(Request $request): bool
    {
        return in_array($request->attributes->get('_route'), ['survey.index', 'survey.test', 'survey.debug']);
    }

    /**
     * @param Request $request
     * @param int     $surveyId
     *
     * @return string|null
     */
    private function getResponseId(Request $request, int $surveyId)
    {
        $result = $request->query->get('id');
        if (!$result) {
            if ($request->hasPreviousSession()) {
                $result = $request->getSession()->get('id:' . $surveyId);
            }
            if (!$result) {
                $result = $request->cookies->get('id:' . $surveyId);
            }
        }

        return $result;
    }
}
