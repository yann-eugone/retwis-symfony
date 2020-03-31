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
