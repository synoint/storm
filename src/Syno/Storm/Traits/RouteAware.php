<?php
namespace Syno\Storm\Traits;

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
}
