<?php

namespace Syno\Storm\Factory;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class NormalizerFactory
{
    /**
     * @var NormalizerInterface[]
     */
    private $normalizers;
    /**
     * NormalizerFactory constructor.
     *
     * @param iterable $normalizers
     */
    public function __construct(iterable $normalizers)
    {
        $this->normalizers = $normalizers;
    }
    /**
     * Returns the normalizer by supported data.
     *
     * @param mixed $data
     *
     * @return NormalizerInterface|null
     */
    public function getNormalizer($data)
    {
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer instanceof NormalizerInterface && $normalizer->supportsNormalization($data)) {
                return $normalizer;
            }
        }
        return null;
    }
}