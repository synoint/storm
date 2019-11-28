<?php

namespace Syno\Storm\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;


class IndexController extends AbstractController implements TokenAuthenticatedController
{
    /**
     * This is used to check availability of API
     *
     * @Route("/api/", name="storm_api.index")
     *
     * @return JsonResponse
     */
    public function index()
    {
        return $this->json('ok');
    }
}
