<?php

declare(strict_types=1);

namespace RocIT\Tests;

use Generator;
use PHPUnit\Framework\TestCase;
use function array_map;
use function implode;
use function is_array;
use function iterator_to_array;
use function RocIT\map;
use function sprintf;

final class IterableMapTest extends TestCase
{
    use IterableMocker;

    public function dataWithoutCallback(): Generator
    {
        yield 'Simple array' => [
            fn() => [['hello', 'world']],
            ['hello', 'world'],
        ];

        yield 'Multiple array' => [
            fn() => [['hello', 'world'], ['plop', 'mister']],
            [['hello', 'plop'], ['world', 'mister']],
        ];

        yield 'Simple iterator' => [
            fn() => [
                self::getGenerator(['hello', 'world']),
            ],
            ['hello', 'world'],
        ];

        yield 'Multiple iterator' => [
            fn() => [
                self::getGenerator(['hello', 'world']),
                self::getGenerator(['plop', 'mister']),
            ],
            [['hello', 'plop'], ['world', 'mister']],
        ];
    }

    /**
     * @covers       map
     * @dataProvider dataWithoutCallback
     */
    public function testSuccessfulWithoutCallback(callable $iteratorsFactory, array $expectedResult): void
    {
        $iterators      = $iteratorsFactory();
        $resultIterator = map(null, ...$iterators);

        self::assertIsIterable($resultIterator);
        self::assertIsNotArray($resultIterator);

        $resultArray = iterator_to_array($resultIterator);

        self::assertEquals($expectedResult, $resultArray);

        $iterators         = $iteratorsFactory();
        $iteratorsAsArrays = array_map(static function ($iterator): array {
            return is_array($iterator) === true ? $iterator : iterator_to_array($iterator);
        }, $iterators);

        self::assertEquals(
            array_map(
                null,
                ...$iteratorsAsArrays,
            ),
            $resultArray
        );
    }

    public function dataWithCallback(): Generator
    {
        $callback = static function (string ...$values): string {
            return sprintf('[%s]', implode(', ', $values));
        };

        yield 'Simple array' => [
            fn() => [['hello', 'world']],
            $callback,
            ['[hello]', '[world]'],
        ];

        yield 'Multiple array' => [
            fn() => [['hello', 'world'], ['plop', 'mister']],
            $callback,
            ['[hello, plop]', '[world, mister]'],
        ];

        yield 'Simple iterator' => [
            fn() => [
                self::getGenerator(['hello', 'world']),
            ],
            $callback,
            ['[hello]', '[world]'],
        ];

        yield 'Multiple iterator' => [
            fn() => [
                self::getGenerator(['hello', 'world']),
                self::getGenerator(['plop', 'mister']),
            ],
            $callback,
            ['[hello, plop]', '[world, mister]'],
        ];
    }

    /**
     * @covers       map
     * @dataProvider dataWithCallback
     */
    public function testSuccessfulWithCallback(callable $iteratorsFactory, ?callable $callback, array $expectedResult): void
    {
        $iterators      = $iteratorsFactory();
        $resultIterator = map($callback, ...$iterators);

        self::assertIsIterable($resultIterator);
        self::assertIsNotArray($resultIterator);

        $resultArray = iterator_to_array($resultIterator);

        self::assertEquals($expectedResult, $resultArray);

        $iterators         = $iteratorsFactory();
        $iteratorsAsArrays = array_map(static function ($iterator): array {
            return is_array($iterator) === true ? $iterator : iterator_to_array($iterator);
        }, $iterators);

        self::assertEquals(
            array_map(
                $callback,
                ...$iteratorsAsArrays,
            ),
            $resultArray
        );
    }
}
