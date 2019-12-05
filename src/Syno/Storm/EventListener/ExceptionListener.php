<?php
namespace Syno\Storm\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

use Syno\Storm\Http\ApiResponse;
use Syno\Storm\Factory\NormalizerFactory;

class ExceptionListener
{
    /**
     * @var NormalizerFactory
     */
    private $normalizerFactory;

    /**
     * ExceptionListener constructor.
     *
     * @param NormalizerFactory $normalizerFactory
     */
    public function __construct(NormalizerFactory $normalizerFactory)
    {
        $this->normalizerFactory = $normalizerFactory;
    }

    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event)
    {
        $throwable = $event->getThrowable();
        $request   = $event->getRequest();
        if (in_array('application/json', $request->getAcceptableContentTypes())) {
            $response = $this->createApiResponse($throwable);
            $event->setResponse($response);
        }
    }

    /**
     * Creates the ApiResponse from any Exception
     *
     * @param \Throwable $throwable
     *
     * @return ApiResponse
     */
    private function createApiResponse(\Throwable $throwable)
    {
        $normalizer = $this->normalizerFactory->getNormalizer($throwable);
        $statusCode = $throwable instanceof HttpExceptionInterface ? $throwable->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;

        try {
            $errors = $normalizer ? $normalizer->normalize($throwable) : [];
        } catch (\Exception $e) {
            $errors = [];
        }
        return new ApiResponse($throwable->getMessage(), null, $errors, $statusCode);
    }
}