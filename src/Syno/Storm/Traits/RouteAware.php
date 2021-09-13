<?php
namespace Syno\Storm\Traits;

use Symfony\Component\HttpFoundation\Request;

trait RouteAware {

    protected function isSurveyEntrance(Request $request): bool
    {
        return $this->isSurveyEntranceRoute($request->attributes->get('_route'));
    }

    protected function isSurveyEntranceRoute(string $route): bool
    {
        return in_array($route, ['survey.index', 'survey.test', 'survey.debug']);
    }

    protected function getLiveEntranceRoute(): string
    {
        return 'survey.index';
    }

    protected function getTestEntranceRoute(): string
    {
        return 'survey.test';
    }

    protected function isDebugRoute(Request $request): bool
    {
        return 'survey.debug' === $request->attributes->get('_route');
    }

    protected function isApiRoute(Request $request): bool
    {
        return false !== strpos($request->attributes->get('_route'), 'storm_api');
    }

    protected function isEmbed(Request $request): bool
    {
        return 'embed' === $request->attributes->get('_route');
    }

    private function isSurveyCompletePage(Request $request): bool
    {
        return $request->attributes->get('_route') === 'survey.complete';
    }

    private function isSurveyScreenOutPage(Request $request): bool
    {
        return $request->attributes->get('_route') === 'survey.screenout';
    }

    private function isSurveyQualityScreenOutPage(Request $request): bool
    {
        return $request->attributes->get('_route') === 'survey.quality_screenout';
    }

    private function isSurveyQuotaFullPage(Request $request): bool
    {
        return $request->attributes->get('_route') === 'survey.quota_full';
    }
}
