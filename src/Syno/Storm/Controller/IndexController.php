<?php

namespace Syno\Storm\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Syno\Storm\Traits\RouteAware;


class IndexController extends AbstractController
{
    use RouteAware;

    /**
     * @Route("/", name="index")
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('b4/index/index.twig');
    }

    /**
     * Clears previous cookies/session, redirects to survey entrance
     *
     * @param Request $request
     * @param RouterInterface $router
     *
     * @Route("/redirect", name="redirect")
     *
     * @return Response
     */
    public function redirectToUrl(Request $request, RouterInterface $router): Response
    {
        $url = $request->query->get('url');
        if (!empty($url) && $request->getHost() === parse_url($url, PHP_URL_HOST)) {
            $route = $router->match(parse_url($url, PHP_URL_PATH));
            if (isset($route['_route']) && $this->isSurveyEntrance($route['_route'])) {
                $response = $this->redirect($url);
            }
        }

        if (!isset($response)) {
            $response = $this->render('b4/index/index.twig');
        }

        foreach ($request->cookies->all() as $name => $value) {
            $response->headers->clearCookie($name);
        }

        return $response;
    }

    /**
     * @param Request $request
     *
     * @Route(
     *     "%app.route_prefix%/embed/{surveyId}",
     *     name="embed",
     *     requirements={"surveyId"="\d+"},
     *     methods={"GET"}
     * )
     *
     * @return RedirectResponse
     */
    public function embed(Request $request): RedirectResponse
    {
        // previous session found in cookie
        if ($request->hasPreviousSession()) {
            return $this->redirectToRoute(
                'survey.index',
                [
                    'surveyId'  => $request->attributes->getInt('surveyId'),
                ]
            );
        }

        // cookies not supported
        if ($request->query->has('_ts')) {
            $request->getSession()->start();
            return $this->redirectToRoute(
                'survey.index',
                [
                    'surveyId'                        => $request->attributes->getInt('surveyId'),
                    $request->getSession()->getName() => $request->getSession()->getId()
                ]
            );
        }

        // cookie test
        $ts = time();
        $request->getSession()->set('_ts', $ts);

        return $this->redirectToRoute(
            'embed',
            [
                'surveyId' => $request->attributes->getInt('surveyId'),
                '_ts'      => $ts
            ]
        );

    }
}
