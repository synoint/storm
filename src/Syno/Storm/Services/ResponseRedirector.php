<?php

namespace Syno\Storm\Services;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Syno\Storm\Traits\RouteAware;
use Syno\Storm\Document\Response;
use Syno\Storm\Document\Survey;
use Symfony\Component\HttpFoundation\RequestStack;

class ResponseRedirector
{
    use RouteAware;

    const COOKIE_CHECK_KEY = 'cookie_check';

    private RouterInterface $router;
    private RequestStack    $requestStack;

    public function __construct(RouterInterface $router, RequestStack $requestStack)
    {
        $this->router       = $router;
        $this->requestStack = $requestStack;
    }

    public function complete(Survey $survey, Response $response = null): RedirectResponse
    {
        if ($response) {
            $url = $survey->getCompleteUrl($response->getSource());
            if ($url) {
                return new RedirectResponse($this->populateParameters($url, $response->getParameters()));
            }
        }

        return new RedirectResponse(
            $this->router->generate('survey.complete', ['surveyId' => $survey->getSurveyId()])
        );
    }

    public function screenOut(Survey $survey, Response $response = null): RedirectResponse
    {
        if ($response) {
            $url = $survey->getScreenoutUrl($response->getSource());
            if ($url) {
                return new RedirectResponse($this->populateParameters($url, $response->getParameters()));
            }
        }

        return new RedirectResponse(
            $this->router->generate('survey.screenout', ['surveyId' => $survey->getSurveyId()])
        );
    }

    public function qualityScreenOut(Survey $survey, Response $response = null): RedirectResponse
    {
        if ($response) {
            $url = $survey->getQualityScreenoutUrl($response->getSource());
            if ($url) {
                return new RedirectResponse($this->populateParameters($url, $response->getParameters()));
            }
        }

        return new RedirectResponse(
            $this->router->generate('survey.quality_screenout', ['surveyId' => $survey->getSurveyId()])
        );
    }

    public function quotaFull(Survey $survey): RedirectResponse
    {
        return new RedirectResponse(
            $this->router->generate('survey.quota_full', ['surveyId' => $survey->getSurveyId()])
        );
    }

    public function page(int $surveyId, int $pageId): RedirectResponse
    {
        $attr = ['surveyId' => $surveyId, 'pageId' => $pageId];

        $request = $this->requestStack->getCurrentRequest();
        if ($request->query->has($request->getSession()->getName())) {
            $attr[$request->getSession()->getName()] = $request->getSession()->getId();
        }

        return new RedirectResponse(
            $this->router->generate('page.index', $attr)
        );
    }

    public function pageUnavailable(): RedirectResponse
    {
        return new RedirectResponse(
            $this->router->generate('page.unavailable')
        );
    }

    public function surveyEntrance(int $surveyId, string $responseMode, array $params = []): RedirectResponse
    {
        $map = [
            Response::MODE_LIVE  => 'survey.index',
            Response::MODE_TEST  => 'survey.test',
            Response::MODE_DEBUG => 'survey.debug',
        ];

        if (!isset($map[$responseMode])) {
            throw new \InvalidArgumentException(sprintf('Unknown mode: "%s"', $responseMode));
        }

        return new RedirectResponse(
            $this->router->generate(
                $map[$responseMode],
                array_merge(['surveyId' => $surveyId], $params)
            )
        );
    }

    public function sessionCookieCheck(int $surveyId): RedirectResponse
    {
        $this->requestStack->getCurrentRequest()->getSession()->set(self::COOKIE_CHECK_KEY, $surveyId);

        return new RedirectResponse(
            $this->router->generate('cookie_check', ['surveyId' => $surveyId, '_cb' => time()])
        );
    }

    private function populateParameters(string $url, Collection $params): string
    {
        foreach ($params as $param) {
            $url = str_replace('{' . $param->getCode() . '}', $param->getValue(), $url);
        }

        return $url;
    }

}
