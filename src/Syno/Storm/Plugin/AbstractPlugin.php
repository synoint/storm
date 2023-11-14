<?php

namespace Syno\Storm\Plugin;

use Symfony\Component\HttpFoundation\Request;

abstract class AbstractPlugin implements PluginInterface
{
    public function onSurveyEntry(Request $request): void
    {
    }
}
