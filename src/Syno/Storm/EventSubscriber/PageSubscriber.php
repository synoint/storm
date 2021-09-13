<?php

namespace Syno\Storm\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Syno\Storm\RequestHandler\Page;
use Syno\Storm\RequestHandler\Survey;

class PageSubscriber implements EventSubscriberInterface
{
    private Page            $pageHandler;
    private Survey          $surveyHandler;
    private RouterInterface $router;

    public function __construct(Page $pageHandler, Survey $surveyHandler, RouterInterface $router)
    {
        $this->pageHandler   = $pageHandler;
        $this->surveyHandler = $surveyHandler;
        $this->router        = $router;
    }

    public function setPage(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if (!$this->pageHandler->hasId()) {
            return;
        }

        if (!$this->surveyHandler->hasSurvey()) {
            throw new \UnexpectedValueException('Survey attribute is not set');
        }

        $pageId = $this->pageHandler->getId();
        $survey = $this->surveyHandler->getSurvey();
        $page   = $survey->getPage($pageId);
        if (!$page) {
            $event->setResponse(new RedirectResponse($this->router->generate('page.unavailable')));

            return;
        }

        $this->pageHandler->setPage($page);
    }

    public function setLocale(RequestEvent $event)
    {
        if (!$this->pageHandler->hasPage()) {
            return;
        }

        $page          = $this->pageHandler->getPage();
        $currentLocale = $event->getRequest()->getLocale();
        $page->setCurrentLocale($currentLocale);
        foreach ($page->getQuestions() as $question) {
            $question->setCurrentLocale($currentLocale);
            foreach ($question->getAnswers() as $answer) {
                $answer->setCurrentLocale($currentLocale);
            }
        }

        $survey         = $this->surveyHandler->getSurvey();
        $fallbackLocale = $survey->getPrimaryLanguageLocale();
        if (null !== $fallbackLocale) {
            $page->setFallbackLocale($fallbackLocale);
            foreach ($page->getQuestions() as $question) {
                $question->setFallbackLocale($fallbackLocale);
                foreach ($question->getAnswers() as $answer) {
                    $answer->setFallbackLocale($fallbackLocale);
                }
            }
        }

        $this->pageHandler->setPage($page);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['setPage', 9],
                ['setLocale']
            ],
        ];
    }
}
