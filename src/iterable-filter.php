<?php

declare(strict_types=1);

namespace RocIT;

use Generator;
use const ARRAY_FILTER_USE_BOTH;
use const ARRAY_FILTER_USE_KEY;

function filter(iterable $iterable, ?callable $callback = null, int $mode = 0): Generator
{
    if ($callback === null) {
        $callback = static function ($value): bool {
            return !empty($value);
        };
    }

    if (ARRAY_FILTER_USE_KEY === $mode) {
        foreach ($iterable as $key => $value) {
            if ($callback($key)) {
                yield $key => $value;
            }
        }
    } elseif (ARRAY_FILTER_USE_BOTH === $mode) {
        foreach ($iterable as $key => $value) {
            if ($callback($value, $key)) {
                yield $key => $value;
            }
        }
    } else {
        foreach ($iterable as $key => $value) {
            if ($callback($value)) {
                yield $key => $value;
            }
        }
    }
}
