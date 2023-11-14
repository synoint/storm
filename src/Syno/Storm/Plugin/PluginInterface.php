<?php

namespace Syno\Storm\Plugin;

use Symfony\Component\HttpFoundation\Request;

interface PluginInterface
{
    public function onSurveyEntry(Request $request): void;
}
