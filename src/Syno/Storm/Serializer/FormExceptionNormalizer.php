<?php

namespace Syno\Storm\Serializer;

use Syno\Storm\Exception\FormException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FormExceptionNormalizer implements NormalizerInterface
{
    /**
     * @param FormException $exception
     * @param string        $format
     * @param array         $context
     *
     * @return array|bool|float|int|string|void
     */
    public function normalize($exception, string $format = null, array $context = [])
    {
        $data   = [];
        $errors = $exception->getErrors();
        foreach ($errors as $error) {
            $data[$error->getOrigin()->getName()][] = $error->getMessage();
        }
        return $data;
    }
    /**
     * @param mixed $data
     * @param string  $format
     *
     * @return bool|void
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof FormException;
    }
}