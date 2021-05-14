<?php

declare(strict_types=1);

namespace RocIT\Tests\Unit;

use Generator;
use Traversable;

trait IterableMocker
{
    private static function getGenerator(array $data): Generator
    {
        yield from $data;
    }

    private static function getTraversable(array $data): Traversable
    {
        yield from $data;
    }
}
