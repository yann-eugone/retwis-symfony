<?php

/**
 * @param array $array
 *
 * @return int[]
 */
function ints(array $array): array
{
    return array_map(fn($value) => (int)$value, $array);
}

/**
 * @param iterable $iterable
 *
 * @return Generator
 */
function flip_generator(iterable $iterable): Generator
{
    foreach ($iterable as $key => $value) {
        yield $value => $key;
    }
}
