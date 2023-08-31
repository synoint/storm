<?php

namespace Syno\Storm\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document;

class Page
{
    private DocumentManager $dm;

    public function __construct(DocumentManager $documentManager)
    {
        $this->dm = $documentManager;
    }

    public function save(Document\Page $page)
    {
        $this->dm->persist($page);
        $this->dm->flush();
    }
}
