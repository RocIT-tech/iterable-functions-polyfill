<?php

declare(strict_types=1);

namespace RocIT;

use ArrayIterator;
use Generator;
use MultipleIterator;
use function func_num_args;
use function is_array;

function map(?callable $callback, ...$iterators): Generator
{
    if (null === $callback && func_num_args() <= 2) {
        foreach ($iterators as $iterator) {
            yield from $iterator;
        }

        return;
    }

    if (null === $callback) {
        $callback = static function (...$values): array {
            return $values;
        };
    }

    $mi = new MultipleIterator(MultipleIterator::MIT_NEED_ANY);
    foreach ($iterators as $iterator) {
        $mi->attachIterator(true === is_array($iterator) ? new ArrayIterator($iterator) : $iterator);
    }

    foreach ($mi as $values) {
        yield $callback(...$values);
    }
}
