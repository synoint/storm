<?php

namespace Syno\Storm\Api\Serializer;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;

class ProblemNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private $debug;
    private $defaultContext = [
        'type' => 'https://tools.ietf.org/html/rfc2616#section-10',
        'title' => 'An error occurred',
    ];

    public function __construct(bool $debug = false, array $defaultContext = [])
    {
        $this->debug = $debug;
        $this->defaultContext = $defaultContext + $this->defaultContext;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($exception, string $format = null, array $context = [])
    {

        $context += $this->defaultContext;

        $debug = $this->debug && $context['debug'] ?? true;

        $data = [
            'type' => $context['type'],
            'title' => $context['title'],
            'status' => $context['status'] ?? $exception->getStatusCode(),
            'detail' => $debug ? $exception->getMessage() : $exception->getStatusText(),
        ];
        if ($debug) {
            $data['class'] = $exception->getClass();
            $data['trace'] = $exception->getTrace();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof FlattenException;
    }

    /**
     * {@inheritdoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
