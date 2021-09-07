<?php

namespace Syno\Storm\Services;

use MongoDB\Collection;
use Symfony\Component\Routing\RouterInterface;
use Syno\Storm\Document;
use Syno\Storm\RequestHandler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Syno\Storm\Traits\RouteAware;

class ResponseState
{
    use RouteAware;

    private RequestHandler\Response $responseRequestHandler;
    private ResponseEventLogger     $responseEventLogger;
    private SurveyEventLogger       $surveyEventLogger;
    private RouterInterface         $router;

    public function __construct(
        RequestHandler\Response $responseRequestHandler,
        ResponseEventLogger     $responseEventLogger,
        SurveyEventLogger       $surveyEventLogger,
        RouterInterface         $router
    ) {
        $this->responseRequestHandler = $responseRequestHandler;
        $this->responseEventLogger    = $responseEventLogger;
        $this->surveyEventLogger      = $surveyEventLogger;
        $this->router                 = $router;
    }

    private function createResponse(Document\Survey $survey, Request $request)
    {
        $response = $this->responseRequestHandler->getNewResponse($request, $survey);
        $response = $this->responseRequestHandler->addUserAgent($request, $response);
        $response->setParameters(
            $this->responseRequestHandler->extractParameters(
                $survey->getParameters(),
                $request
            )
        );
        $this->responseRequestHandler->saveResponse($response);
        $this->responseRequestHandler->setResponse($request, $response);

        $this->responseEventLogger->log(ResponseEventLogger::RESPONSE_CREATED, $response);
        $this->responseEventLogger->log(ResponseEventLogger::SURVEY_ENTERED, $response);
        $this->surveyEventLogger->logResponse($response, $survey);
    }

    private function clearResponse(Document\Response $response, Request $request)
    {
        $this->responseRequestHandler->clearResponse($request);
        $this->responseRequestHandler->clearResponseIdInSession($request, $response->getSurveyId());
        $request->getSession()->migrate(true);
        $this->responseEventLogger->log(ResponseEventLogger::RESPONSE_CLEARED, $response);
    }

    public function redirectOnFinishedResponseAndWrongUrl(
        Document\Response $response,
        Request $request
    ):? RedirectResponse
    {
        $redirect = null;
        if ($response->isCompleted() && !$this->isSurveyCompletePage($request)) {
            $redirect = new RedirectResponse(
                $this->router->generate('survey.complete', ['surveyId' => $response->getSurveyId()])
            );
        } elseif ($response->isScreenedOut() && !$this->isSurveyScreenOutPage($request)) {
            $redirect = new RedirectResponse(
                $this->router->generate('survey.screenout', ['surveyId' => $response->getSurveyId()])
            );
        } elseif ($response->isQualityScreenedOut() && !$this->isSurveyQualityScreenOutPage($request)) {
            $redirect = new RedirectResponse(
                $this->router->generate('survey.quality_screenout', ['surveyId' => $response->getSurveyId()])
            );
        } elseif ($response->isQuotaFull() && !$this->isSurveyQuotaFullPage($request)) {
            $redirect = new RedirectResponse(
                $this->router->generate('survey.quality_screenout', ['surveyId' => $response->getSurveyId()])
            );
        }

        return $redirect;
    }

    public function switchSurveyVersion(Document\Survey $survey, Document\Response $response)
    {
        $this->responseEventLogger->log(ResponseEventLogger::SURVEY_VERSION_UNAVAILABLE, $response);

        $response
            ->setSurveyVersion($survey->getVersion())
            ->setPageId(null)
            ->setPageCode(null)
            ->clearAnswers();

        $this->responseRequestHandler->saveResponse($response);

        $this->responseEventLogger->log(ResponseEventLogger::ANSWERS_CLEARED, $response);

        // we need to log this response again, because previous version is gone
        $this->surveyEventLogger->logResponse($response, $survey);
    }

    public function saveAnswers(Document\Response $response, Collection $questions, array $formData)
    {
        foreach ($questions as $question) {
            $answers = $this->responseRequestHandler->extractQuestionAnswers($question, $formData);
            $response->addAnswer(new Document\ResponseAnswer($question->getQuestionId(), $answers));
        }

        $this->responseRequestHandler->saveResponse($response);
        $this->responseEventLogger->log(ResponseEventLogger::ANSWERS_SAVED, $response);
    }

    public function saveResponseProgress(Document\Response $response, Document\Page $page)
    {
        if ($response->getPageId() !== $page->getPageId()) {
            $response->setPageId($page->getPageId());
            $response->setPageCode($page->getCode());
            $this->responseRequestHandler->saveResponse($response);

            $this->responseEventLogger->log(ResponseEventLogger::PAGE_ENTERED, $response);
        }
    }

    public function complete(
        Document\Survey $survey,
        Document\Response $response,
        Request $request
    ): RedirectResponse
    {
        $response->setCompleted(true);
        $this->responseRequestHandler->saveResponse($response);
        $this->responseRequestHandler->setResponse($request, $response);

        $this->responseEventLogger->log(ResponseEventLogger::SURVEY_COMPLETED, $response);
        $this->surveyEventLogger->logComplete($response, $survey);

        $completeUrl = $survey->getCompleteUrl($response->getSource());
        if ($completeUrl) {
            return $this->redirect($this->populateParameters($completeUrl, $response));
        }

        return $this->redirectToRoute('survey.complete', ['surveyId' => $survey->getSurveyId()]);
    }

    public function qualityScreenOut(
        Document\Survey $survey,
        Document\Response $response,
        Request $request
    ): RedirectResponse
    {
        $response->setQualityScreenedOut(true);
        $this->responseRequestHandler->saveResponse($response);
        $this->responseRequestHandler->setResponse($request, $response);

        $this->responseEventLogger->log(ResponseEventLogger::SURVEY_QUALITY_SCREENOUTED, $response);
        $this->surveyEventLogger->log(SurveyEventLogger::QUALITY_SCREENOUT, $survey);

        $url = $survey->getQualityScreenoutUrl($response->getSource());
        if ($url) {
            return $this->redirect($this->populateParameters($url, $response));
        }

        return $this->redirectToRoute('survey.quality_screenout', ['surveyId' => $survey->getSurveyId()]);
    }

    public function screenOut(
        Document\Survey $survey,
        Document\Response $response,
        Request $request
    ): RedirectResponse
    {
        $response->setScreenedOut(true);
        $this->responseRequestHandler->saveResponse($response);
        $this->responseRequestHandler->setResponse($request, $response);

        $this->responseEventLogger->log(ResponseEventLogger::SURVEY_SCREENOUTED, $response);
        $this->surveyEventLogger->log(SurveyEventLogger::SCREENOUT, $survey);

        $url = $survey->getScreenoutUrl($response->getSource());
        if ($url) {
            return $this->redirect($this->populateParameters($url, $response));
        }

        return $this->redirectToRoute('survey.screenout', ['surveyId' => $survey->getSurveyId()]);
    }

    protected function redirectToRoute(string $route, array $parameters = []): RedirectResponse
    {
        return $this->redirect($this->generateUrl($route, $parameters));
    }

    protected function redirect(string $url, int $status = 302): RedirectResponse
    {
        return new RedirectResponse($url, $status);
    }

    protected function generateUrl(string $route, array $parameters = []): string
    {
        return $this->router->generate($route, $parameters);
    }

    protected function populateParameters(string $url, Document\Response $response): string
    {
        foreach ($response->getParameters() as $parameter) {
            $url = str_replace('{' . $parameter->getCode() . '}', $parameter->getValue(), $url);
        }

        return $url;
    }

}
