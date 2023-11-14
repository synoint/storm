<?php

namespace Plugins\Survey_123456;

use Symfony\Component\HttpFoundation\Request;
use Syno\Storm\Plugin\AbstractPlugin;
use Syno\Storm\RequestHandler\Response;

class ExamplePlugin extends AbstractPlugin
{
    private Response $responseHandler;

    public function __construct(Response $responseHandler)
    {
        $this->responseHandler = $responseHandler;
    }

    public function onSurveyEntry(Request $request): void
    {
        dd($request->query->all());
    }
}
