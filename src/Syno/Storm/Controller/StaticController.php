<?php

namespace Syno\Storm\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Syno\Storm\Document;

class StaticController extends AbstractController
{
    /**
     * @Route("%app.route_prefix%/c", name="static.complete")
     *
     * @return Response
     */
    public function complete(): Response
    {
        return $this->render(Document\Config::DEFAULT_THEME . '/static/complete.twig');
    }

    /**
     * @Route("%app.route_prefix%/sc", name="static.screenout")
     *
     * @return Response
     */
    public function screenout(): Response
    {
        return $this->render(Document\Config::DEFAULT_THEME . '/static/screenout.twig');
    }

    /**
     * @Route("%app.route_prefix%/qf", name="static.quota_full")
     *
     * @return Response
     */
    public function quotafull(): Response
    {
        return $this->render(Document\Config::DEFAULT_THEME . '/static/quota_full.twig');
    }

    /**
     * @Route("%app.route_prefix%/404", name="static.unavailable")
     *
     * @return Response|RedirectResponse
     */
    public function unavailable()
    {
        return $this->render(Document\Config::DEFAULT_THEME . '/static/unavailable.twig');
    }
}
