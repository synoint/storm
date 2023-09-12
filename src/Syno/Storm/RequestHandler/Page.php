<?php

namespace Syno\Storm\RequestHandler;

use Symfony\Component\HttpFoundation\RequestStack;
use Syno\Storm\Document;

class Page
{
    CONST ATTR = 'page';

    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getPage(): Document\PageInterface
    {
        $page = $this->requestStack->getCurrentRequest()->attributes->get(self::ATTR);
        if (!$page instanceof Document\PageInterface) {
            throw new \UnexpectedValueException('Page attribute is invalid');
        }

        return $page;
    }

    public function setPage(Document\PageInterface $page)
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



}
