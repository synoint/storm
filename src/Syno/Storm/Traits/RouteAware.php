<?php
namespace Syno\Storm\Traits;

use Symfony\Component\HttpFoundation\Request;

trait RouteAware {

    /**
     * @param string $route
     *
     * @return bool
     */
    protected function isSurveyEntrance(string $route): bool
    {
        return in_array($route, ['survey.index', 'survey.test', 'survey.debug']);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function isDebugRoute(Request $request)
    {
        return 'survey.debug' === $request->attributes->get('_route');
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function isApiRoute(Request $request)
    {
        return false !== strpos($request->attributes->get('_route'), 'storm_api');
    }
}
