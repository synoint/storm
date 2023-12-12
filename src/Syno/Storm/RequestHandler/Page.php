<?php

namespace Syno\Storm\RequestHandler;

use Symfony\Component\HttpFoundation\RequestStack;
use Syno\Storm\Document;
use Syno\Storm\Services;

class Page
{
    CONST ATTR = 'page';

    private RequestStack        $requestStack;
    private Services\PageFinder $pageFinder;

    public function __construct(RequestStack $requestStack, Services\PageFinder $pageFinder)
    {
        $this->requestStack = $requestStack;
        $this->pageFinder   = $pageFinder;
    }


    public function getPage(): Document\Page
    {
        $page = $this->requestStack->getCurrentRequest()->attributes->get(self::ATTR);
        if (!$page instanceof Document\Page) {
            throw new \UnexpectedValueException('Page attribute is invalid');
        }

        return $page;
    }

    public function setPage(Document\Page $page)
    {
        $this->requestStack->getCurrentRequest()->attributes->set(self::ATTR, $page);
    }

    public function hasPage(): bool
    {
        return $this->requestStack->getCurrentRequest()->attributes->has(self::ATTR);
    }

    public function hasId(): bool
    {
        return $this->requestStack->getCurrentRequest()->attributes->has('pageId');
    }

    public function getId(): int
    {
        return $this->requestStack->getCurrentRequest()->attributes->getInt('pageId');
    }

    public function getFirstPageId():? int
    {
        return $this->pageFinder->getFirstPageId();
    }

    public function getNextPageId():? int
    {
        return $this->pageFinder->getNextPageId($this->getId());
    }

    public function getLastPageId():? int
    {
        return $this->pageFinder->getLastPageId();
    }

    public function findPage(int $surveyId, int $version, int $pageId):? Document\Page
    {
        return $this->pageFinder->findPage($surveyId, $version, $pageId);
    }


}
