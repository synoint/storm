<?php

namespace Syno\Storm\RequestHandler;

use Symfony\Component\HttpFoundation\Request;
use Syno\Storm\Document;

class Page
{
    CONST ATTR = 'page';

    public function hasPageId(Request $request): bool
    {
        return $request->attributes->has('pageId');
    }

    public function getPageId(Request $request): int
    {
        return $request->attributes->getInt('pageId');
    }

    public function setPage(Request $request, Document\Page $page)
    {
        $request->attributes->set(self::ATTR, $page);
    }

    public function getPage(Request $request): Document\Page
    {
        $page = $request->attributes->get(self::ATTR);
        if (!$page instanceof Document\Page) {
            throw new \UnexpectedValueException('Page attribute is invalid');
        }

        return $page;
    }

}
