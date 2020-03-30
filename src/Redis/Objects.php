<?php

namespace App\Redis;

use Predis\ClientInterface;
use RuntimeException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function is_array;

final class Objects
{
    private NormalizerInterface $normalizer;

    private DenormalizerInterface $denormalizer;

    private ObjectKeys $key;

    private ClientInterface $redis;

    public function __construct(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        ObjectKeys $key,
        ClientInterface $redis
    ) {
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
        $this->key = $key;
        $this->redis = $redis;
    }

    public function add(string $identity, object $object): void
    {
        $key = $this->key($this->key->object($object), $identity);
        $dictionary = $this->dictionary($object);

        $this->redis->hmset($key, $dictionary);
    }

    public function update(string $identity, object $object): void
    {
        $key = $this->key($this->key->object($object), $identity);
        $dictionary = $this->dictionary($object);

        $this->redis->hmset($key, $dictionary);
    }

    public function get(string $class, string $identity): object
    {
        $key = $this->key($this->key->class($class), $identity);
        $dictionary = $this->redis->hgetall($key);
        if (!$dictionary) {
            throw new NotFoundException(
                sprintf('There is no object %s.', $key)
            );
        }

        return $this->object($class, $dictionary);
    }

    private function key(string $key, string $identity): string
    {
        return $key . ':' . $identity;
    }

    private function dictionary(object $object): array
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

    private function object(string $class, array $dictionary): object
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
