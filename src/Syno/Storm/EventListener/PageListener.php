<?php

namespace Syno\Storm\EventListener;

use PhpParser\Comment\Doc;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Syno\Storm\Document;
use Syno\Storm\RequestHandler\Page;
use Syno\Storm\RequestHandler\Survey;

class PageListener implements EventSubscriberInterface
{
    /** @var Page */
    private $pageRequestHandler;

    /** @var Survey */
    private $surveyRequestHandler;

    /** @var RouterInterface */
    private $router;

    /**
     * @param Page            $pageRequestHandler
     * @param Survey          $surveyRequestHandler
     * @param RouterInterface $router
     */
    public function __construct(Page $pageRequestHandler, Survey $surveyRequestHandler, RouterInterface $router)
    {
        $this->pageRequestHandler   = $pageRequestHandler;
        $this->surveyRequestHandler = $surveyRequestHandler;
        $this->router               = $router;
    }


    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        /** @var Request $request */
        $request = $event->getRequest();

        if (!$this->pageRequestHandler->hasPageId($request)) {
            return;
        }

        if (!$this->surveyRequestHandler->hasSurvey($request)) {
            throw new \UnexpectedValueException('Survey attribute is not set');
        }

        $pageId = $this->pageRequestHandler->getPageId($request);
        if ($pageId) {
            $survey = $this->surveyRequestHandler->getSurvey($request);
            $page = $survey->getPage($pageId);
            if ($page) {
                $page = $this->setLocale($page, $request->getLocale(), $survey->getPrimaryLanguageLocale());
                $this->pageRequestHandler->setPage($request, $page);
                return;
            }
        }

        $event->setResponse(new RedirectResponse($this->router->generate('page.unavailable')));
    }

    /**
     * @param Document\Page $page
     * @param string        $currentLocale
     * @param string|null   $fallbackLocale
     *
     * @return Document\Page
     */
    protected function setLocale(Document\Page $page, string $currentLocale, string $fallbackLocale = null)
    {
        $page->setCurrentLocale($currentLocale);
        /** @var Document\Question $question */
        foreach ($page->getQuestions() as $question) {
            $question->setCurrentLocale($currentLocale);
            /** @var Document\Answer $answer */
            foreach ($question->getAnswers() as $answer) {
                $answer->setCurrentLocale($currentLocale);
            }
        }

        if (null !== $fallbackLocale) {
            $page->setFallbackLocale($fallbackLocale);
            /** @var Document\Question $question */
            foreach ($page->getQuestions() as $question) {
                $question->setFallbackLocale($currentLocale);
                /** @var Document\Answer $answer */
                foreach ($question->getAnswers() as $answer) {
                    $answer->setFallbackLocale($currentLocale);
                }
            }
        }

        return $page;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 9],
        ];
    }
}
