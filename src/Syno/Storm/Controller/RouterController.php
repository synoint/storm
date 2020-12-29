<?php

namespace Syno\Storm\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RouterController extends AbstractController
{
    /**
     * @Route("%app.route_prefix%/c", name="router.complete")
     * @return Response|RedirectResponse
     */
    public function complete()
    {
        return $this->render('b4/router/complete.twig');
    }

    /**
     * @Route("%app.route_prefix%/sc", name="router.screenout")
     * @return Response|RedirectResponse
     */
    public function screenout()
    {
        return $this->render( 'b4/router/screenout.twig');
    }

    /**
     * @Route("%app.route_prefix%/qf", name="router.quota_full")
     * @return Response|RedirectResponse
     */
    public function quotafull()
    {
        return $this->render('b4/router/quota_full.twig');
    }
}
