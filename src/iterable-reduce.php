<?php

declare(strict_types=1);

namespace RocIT;

use function is_array;

function reduce(iterable $iterator, callable $callback, mixed $initial = null): mixed
{
    if (is_array($iterator) === true) {
        return \array_reduce($iterator, $callback, $initial);
    }

    $result = $initial;

    foreach ($iterator as $value) {
        $result = $callback($result, $value);
    }

    return $result;
}
