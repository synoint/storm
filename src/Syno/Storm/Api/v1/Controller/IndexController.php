<?php

namespace Syno\Storm\Api\v1\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Syno\Storm\Api\Controller\TokenAuthenticatedController;

/**
 * @Route("/api/v1")
 */
class IndexController extends AbstractController implements TokenAuthenticatedController
{
    /**
     * This is used to check availability of API
     *
     * @Route("", name="storm_api.v1.index")
     */
    public function index(): JsonResponse
    {
        return $this->json('ok');
    }
}
