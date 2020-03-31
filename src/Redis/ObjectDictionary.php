<?php

namespace App\Redis;

use RuntimeException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function is_array;

final class ObjectDictionary
{
    private NormalizerInterface $normalizer;

    private DenormalizerInterface $denormalizer;

    public function __construct(NormalizerInterface $normalizer, DenormalizerInterface $denormalizer)
    {
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
    }

    public function dictionary(object $object): array
    {
        try {
            $dictionary = $this->normalizer->normalize($object, null, ['groups' => ['redis']]);
        } catch (ExceptionInterface $exception) {
            throw new RuntimeException($exception->getMessage(), 0, $exception);
        }

        if (!is_array($dictionary)) {
            throw new RuntimeException('Expecting object normalization is returning an array');
        }

        return $dictionary;
    }

    public function object(string $class, array $dictionary): object
    {
        try {
            $object = $this->denormalizer->denormalize(
                $dictionary,
                $class,
                null,
                [
                    'groups' => ['redis'],
                    AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
                ]
            );
        } catch (ExceptionInterface $exception) {
            throw new RuntimeException($exception->getMessage(), 0, $exception);
        }

        if (!is_object($object)) {
            throw new RuntimeException('Expecting dictionary denormalization is returning an object');
        }

        return $object;
    }
}
