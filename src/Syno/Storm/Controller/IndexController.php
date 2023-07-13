<?php

namespace Syno\Storm\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Syno\Storm\Services\ResponseRedirector;
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
            if (isset($route['_route']) && $this->isSurveyEntranceRoute($route['_route'])) {
                // Getting all params here because:
                // $request->query->get cuts other params except first parameter because of ? symbol in inner url
                $params = $request->query->all();
                unset($params['url']);
                $queryString = http_build_query($params);

                $response = $this->redirect($url.'&'.$queryString);
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
     *     "%app.route_prefix%/cookie_check/{surveyId}",
     *     name="cookie_check",
     *     requirements={"surveyId"="\d+"},
     *     methods={"GET"}
     * )
     *
     * @return RedirectResponse
     */
    public function cookieCheck(Request $request): RedirectResponse
    {
        $surveyId = $request->attributes->getInt('surveyId');
        // previous session found
        if ($request->hasPreviousSession() &&
            $surveyId === $request->getSession()->get(ResponseRedirector::COOKIE_CHECK_KEY)
        ) {
            return $this->redirectToRoute(
                'survey.index',
                [
                    'surveyId'  => $surveyId,
                ]
            );
        }

        // cookies not supported, redirect with session ID in URL
        $request->getSession()->start();
        return $this->redirectToRoute(
            'survey.index',
            [
                'surveyId'                        => $surveyId,
                $request->getSession()->getName() => $request->getSession()->getId()
            ]
        );
    }
}
