<?php

namespace Syno\Storm\RequestHandler;

use Symfony\Component\HttpFoundation\Request;
use Syno\Storm\Document;

class Page
{
    CONST ATTR = 'page';

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function hasPageId(Request $request)
    {
        return $request->attributes->has('pageId');
    }

    /**
     * @param Request $request
     *
     * @return int
     */
    public function getPageId(Request $request)
    {
        return $request->attributes->getInt('pageId');
    }

    /**
     * @param Request       $request
     * @param Document\Page $page
     */
    public function setPage(Request $request, Document\Page $page)
    {
        $request->attributes->set(self::ATTR, $page);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function hasPage(Request $request)
    {
        return $request->attributes->has(self::ATTR);
    }

    /**
     * @param Request $request
     *
     * @return Document\Page
     */
    public function getPage(Request $request)
    {
        $page = $request->attributes->get(self::ATTR);
        if (!$page instanceof Document\Page) {
            throw new \UnexpectedValueException('Page attribute is invalid');
        }

        return $page;
    }


}
