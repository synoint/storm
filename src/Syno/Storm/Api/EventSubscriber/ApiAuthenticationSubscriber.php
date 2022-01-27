<?php

namespace Syno\Storm\Api\EventSubscriber;

use Syno\Storm\Api\Controller\TokenAuthenticatedController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;


class ApiAuthenticationSubscriber implements EventSubscriberInterface
{
    private $apiAccessToken;

    public function __construct($apiAccessToken)
    {
        $this->apiAccessToken = $apiAccessToken;
    }


    public function onKernelController(ControllerEvent $event)
    {
        $controller = $event->getController();

        // when a controller class defines multiple action methods, the controller
        // is returned as [$controllerInstance, 'methodName']
        if (is_array($controller)) {
            $controller = $controller[0];
        }

        if ($controller instanceof TokenAuthenticatedController) {
            $token = $event->getRequest()->headers->get('Access-Token');
            if ($token !== $this->apiAccessToken) {
                throw new AccessDeniedHttpException('Access-Token required!');
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
