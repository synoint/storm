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
     * @Route("", name="storm_api.index")
     *
     * @return JsonResponse
     */
    public function index()
    {
        return $this->json('ok');
    }
}
