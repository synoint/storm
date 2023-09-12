<?php

namespace Syno\Storm\Document;

use Doctrine\Common\Collections\Collection;

interface PageInterface
{
    public function getPageId(): ?int;

    public function getSortOrder(): ?int;

    public function getContent();

    public function getJavascript();

    public function hasMedia(): bool;

    public function getQuestions(): Collection;

    public function getVisibleQuestions(): Collection;

    public function getTranslations();
}
